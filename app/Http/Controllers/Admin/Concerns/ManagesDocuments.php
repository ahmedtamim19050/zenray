<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared data for the quote and invoice editors.
 */
trait ManagesDocuments
{
    /**
     * @return array<string, mixed>
     */
    protected function editorData(Model $document): array
    {
        return [
            'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->orderBy('name')
                ->get(['id', 'name', 'organization', 'email', 'phone']),
            // Picker options for line items, with the price and stock the editor fills in.
            'inventory' => InventoryItem::query()->where('is_active', true)->orderBy('name')
                ->get(['id', 'sku', 'name', 'description', 'unit', 'sale_price', 'quantity'])
                ->map(fn (InventoryItem $item) => [
                    'id' => $item->id,
                    'label' => "{$item->sku} · {$item->name}",
                    'description' => $item->description ? "{$item->name} – {$item->description}" : $item->name,
                    'unit' => $item->unit,
                    'price' => (float) $item->sale_price,
                    'stock' => $item->quantity,
                ]),
            // Services have no stored price and no stock, so the editor only fills in the name.
            'services' => Service::query()->active()->ordered()->pluck('name'),
            'items' => old('items', $document->exists
                ? $document->items->map->only(['inventory_item_id', 'description', 'quantity', 'unit', 'unit_price'])->all()
                : [['inventory_item_id' => null, 'description' => '', 'quantity' => 1, 'unit' => null, 'unit_price' => '']]),
        ];
    }

    /**
     * Defaults for a brand-new document.
     *
     * @return array<string, mixed>
     */
    protected function documentDefaults(): array
    {
        return [
            'status' => 'draft',
            'issue_date' => today(),
            'currency' => config('zenray.billing.currency'),
            'tax_rate' => config('zenray.billing.tax_rate'),
            'discount' => 0,
        ];
    }

    /**
     * Customer fields copied from a registered customer account.
     *
     * @return array<string, mixed>
     */
    protected function customerFields(?User $customer): array
    {
        return $customer ? [
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_organization' => $customer->organization,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
        ] : [];
    }
}
