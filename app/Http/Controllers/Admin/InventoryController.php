<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public const FILTERS = ['website' => 'Website products', 'low' => 'Low stock', 'out' => 'Out of stock', 'inactive' => 'Inactive'];

    public function index(Request $request): View
    {
        $filter = $request->string('stock')->toString();
        $search = trim($request->string('q')->toString());

        $items = $this->filtered($filter, $search)->orderBy('name')->paginate(20)->withQueryString();

        $totals = InventoryItem::query()->where('is_active', true)
            ->selectRaw('count(*) as items, sum(case when quantity > 0 then quantity else 0 end) as units')
            ->selectRaw('sum(case when quantity > 0 then quantity * cost_price else 0 end) as cost_value')
            ->selectRaw('sum(case when quantity > 0 then quantity * sale_price else 0 end) as sale_value')
            ->first();

        return view('admin.inventory.index', [
            'items' => $items,
            'filter' => $filter,
            'search' => $search,
            'totals' => $totals,
            'lowCount' => InventoryItem::query()->where('is_active', true)->lowStock()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.inventory.form', ['item' => new InventoryItem(['unit' => 'pcs', 'is_active' => true]), 'products' => $this->products()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, new InventoryItem);
        $opening = (int) $request->validate(['opening_stock' => ['nullable', 'integer', 'min:0', 'max:1000000']])['opening_stock'];

        $item = DB::transaction(function () use ($data, $opening, $request) {
            $item = InventoryItem::create($data);

            if ($opening > 0) {
                $item->adjust($opening, 'opening', ['user_id' => $request->user()->id, 'unit_cost' => $item->cost_price]);
            }

            return $item;
        });

        return to_route('admin.inventory.show', $item)->with('status', "{$item->name} added to inventory.");
    }

    public function show(InventoryItem $inventory): View
    {
        return view('admin.inventory.show', [
            'item' => $inventory->load('product'),
            'movements' => $inventory->movements()->with(['user', 'invoice'])->paginate(20),
            'sold' => (int) -$inventory->movements()->getQuery()->whereIn('type', ['sold', 'returned'])->sum('quantity'),
        ]);
    }

    public function edit(InventoryItem $inventory): View
    {
        return view('admin.inventory.form', ['item' => $inventory, 'products' => $this->products()]);
    }

    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $inventory->update($this->validated($request, $inventory));

        return to_route('admin.inventory.show', $inventory)->with('status', 'Item saved.');
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        if ($inventory->movements()->getQuery()->whereNotNull('invoice_id')->exists()) {
            return back()->withErrors(['item' => 'This item appears on invoices. Mark it as inactive instead of deleting it.']);
        }

        $inventory->delete();

        return to_route('admin.inventory.index')->with('status', "{$inventory->name} deleted.");
    }

    /**
     * Receive stock, remove stock (damage, loss, own use) or record a stock count.
     */
    public function adjust(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['received', 'removed', 'counted'])],
            'quantity' => ['required', 'integer', $request->input('type') === 'counted' ? 'min:0' : 'min:1', 'max:1000000'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $context = ['user_id' => $request->user()->id] + collect($data)->only(['unit_cost', 'reference', 'note'])->filter(fn ($v) => filled($v))->all();

        $movement = match ($data['type']) {
            'received' => $inventory->adjust((int) $data['quantity'], 'received', $context),
            'removed' => $inventory->adjust(-(int) $data['quantity'], 'removed', $context),
            'counted' => $inventory->countAs((int) $data['quantity'], $context),
        };

        // Receiving stock at a new cost updates the item's cost price.
        if ($data['type'] === 'received' && filled($data['unit_cost'] ?? null)) {
            $inventory->update(['cost_price' => $data['unit_cost']]);
        }

        return back()->with('status', $movement
            ? "Stock updated. {$inventory->name} now has {$inventory->fresh()->quantity} {$inventory->unit}."
            : 'The count matches the current stock. Nothing changed.');
    }

    public function export(Request $request): StreamedResponse
    {
        $items = $this->filtered($request->string('stock')->toString(), trim($request->string('q')->toString()))->orderBy('name')->get();

        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['SKU', 'Name', 'Unit', 'Quantity', 'Reorder level', 'Cost price', 'Sale price', 'Stock value (cost)', 'Location', 'Status']);

            foreach ($items as $item) {
                fputcsv($out, [$item->sku, $item->name, $item->unit, $item->quantity, $item->reorder_level, $item->cost_price, $item->sale_price,
                    number_format($item->stockValue(), 2, '.', ''), $item->location, $item->is_active ? $item->stockLabel() : 'Inactive']);
            }

            fclose($out);
        }, 'inventory-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filtered(string $filter, string $search)
    {
        return InventoryItem::query()
            ->when($filter === 'inactive', fn ($q) => $q->where('is_active', false), fn ($q) => $q->where('is_active', true))
            ->when($filter === 'website', fn ($q) => $q->whereNotNull('product_id'))
            ->when($filter === 'low', fn ($q) => $q->lowStock())
            ->when($filter === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->when($search !== '', fn ($q) => $q->search($search));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, InventoryItem $item): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:60', 'alpha_dash:ascii', Rule::unique('inventory_items')->ignore($item->id)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'product_id' => ['nullable', 'exists:products,id'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        return array_merge($data, [
            'sku' => strtoupper($data['sku']),
            'reorder_level' => $data['reorder_level'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function products(): array
    {
        return Product::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
