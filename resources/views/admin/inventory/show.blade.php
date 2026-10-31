@php
    $input = 'block w-full rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-navy-900 shadow-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none';
    $margin = $item->margin();
@endphp

<x-layouts.dashboard :title="$item->name" area="admin">
    <x-slot:subtitle>
        <a href="{{ route('admin.inventory.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← Inventory</a>
        <span class="font-mono text-slate-500">· {{ $item->sku }}</span>
    </x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.inventory.edit', $item)" variant="navy" size="sm">Edit item</x-button>
    </x-slot:actions>

    @if ($errors->has('item'))
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">{{ $errors->first('item') }}</div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dash.stat label="In stock" :value="number_format($item->quantity).' '.$item->unit" icon="layers" :tone="$item->isLow() ? 'signal' : 'navy'" :hint="$item->is_active ? $item->stockLabel().($item->reorder_level ? ' · reorder at '.$item->reorder_level : '') : 'Inactive'" />
        <x-dash.stat label="Stock value" :value="\App\Support\Money::format($item->stockValue())" icon="folder" tone="slate" hint="At cost price" />
        <x-dash.stat label="Sale price" :value="\App\Support\Money::format($item->sale_price)" icon="check" tone="sky" :hint="'Cost '.\App\Support\Money::format($item->cost_price).($margin !== null ? ' · '.$margin.'% margin' : '')" />
        <x-dash.stat label="Sold on invoices" :value="number_format($sold)" icon="document" tone="slate" :hint="$item->unit.' in total'" />
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <x-dash.card title="Stock history" class="xl:col-span-2" :padded="false">
            @if ($movements->isEmpty())
                <x-dash.empty icon="clock" title="No stock movements yet" text="Received stock, sales and counts will be listed here." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Type</th>
                                <th scope="col" class="px-6 py-3">Reference</th>
                                <th scope="col" class="px-6 py-3 text-right">Change</th>
                                <th scope="col" class="px-6 py-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-50">
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $movement->created_at->format('j M Y, H:i') }}<p class="text-xs text-slate-400">{{ $movement->user?->name }}</p></td>
                                    <td class="px-6 py-3.5"><x-admin.badge :state="[$movement->typeLabel(), $movement->typeClasses()]" /></td>
                                    <td class="px-6 py-3.5 text-slate-600">
                                        @if ($movement->invoice)
                                            <a href="{{ route('admin.invoices.show', $movement->invoice) }}" class="font-mono font-semibold text-navy-800 hover:text-signal-700">{{ $movement->invoice->number }}</a>
                                        @else
                                            {{ $movement->reference ?: '—' }}
                                        @endif
                                        @if ($movement->note) <p class="text-xs text-slate-400">{{ $movement->note }}</p> @endif
                                    </td>
                                    <td @class(['px-6 py-3.5 text-right font-semibold whitespace-nowrap tabular-nums', 'text-emerald-700' => $movement->quantity > 0, 'text-red-700' => $movement->quantity < 0])>{{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}</td>
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap text-navy-900 tabular-nums">{{ number_format($movement->balance) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($movements->hasPages())
                    <div class="border-t border-navy-100 px-6 py-4">{{ $movements->links() }}</div>
                @endif
            @endif
        </x-dash.card>

        <div class="grid gap-6 xl:sticky xl:top-24">
            <x-dash.card title="Adjust stock">
                <form method="POST" action="{{ route('admin.inventory.adjust', $item) }}" class="grid gap-4" novalidate>
                    @csrf
                    <fieldset>
                        <legend class="sr-only">Type of change</legend>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['received' => 'Receive', 'removed' => 'Remove', 'counted' => 'Count'] as $key => $label)
                                <label class="flex cursor-pointer items-center justify-center rounded-xl px-2 py-2 text-sm font-semibold text-slate-600 ring-1 ring-navy-200 transition has-checked:bg-navy-800 has-checked:text-white has-checked:ring-navy-800">
                                    <input type="radio" name="type" value="{{ $key }}" @checked(old('type', 'received') === $key) class="sr-only"> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Receive adds stock. Remove takes it out (damaged, lost, own use). Count sets the exact figure after a stocktake.</p>
                    </fieldset>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="adj-qty" class="mb-1 block text-xs font-semibold text-slate-500">Quantity ({{ $item->unit }})</label>
                            <input type="number" id="adj-qty" name="quantity" value="{{ old('quantity') }}" min="0" step="1" required class="{{ $input }} tabular-nums">
                        </div>
                        <div>
                            <label for="adj-cost" class="mb-1 block text-xs font-semibold text-slate-500">Unit cost (optional)</label>
                            <input type="number" id="adj-cost" name="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01" class="{{ $input }} tabular-nums" placeholder="{{ $item->cost_price }}">
                        </div>
                    </div>
                    <div>
                        <label for="adj-ref" class="mb-1 block text-xs font-semibold text-slate-500">Reference (optional)</label>
                        <input type="text" id="adj-ref" name="reference" value="{{ old('reference') }}" maxlength="120" class="{{ $input }}" placeholder="Supplier invoice or delivery note">
                    </div>
                    <div>
                        <label for="adj-note" class="mb-1 block text-xs font-semibold text-slate-500">Note (optional)</label>
                        <input type="text" id="adj-note" name="note" value="{{ old('note') }}" maxlength="255" class="{{ $input }}">
                    </div>
                    @foreach (['type', 'quantity', 'unit_cost', 'reference', 'note'] as $field)
                        @error($field) <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                    @endforeach
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-full bg-signal-500 px-5 text-sm font-semibold text-navy-950 hover:bg-signal-400">Update stock</button>
                </form>
            </x-dash.card>

            <x-dash.card title="Details">
                <dl class="space-y-2.5 text-sm">
                    @foreach (['Location' => $item->location, 'Unit' => $item->unit, 'Website product' => $item->product?->name, 'Added' => $item->created_at->format('j M Y')] as $label => $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right text-navy-900">{{ filled($value) ? $value : '—' }}</dd></div>
                    @endforeach
                </dl>
                @if ($item->description) <p class="mt-4 border-t border-navy-100 pt-4 text-sm whitespace-pre-line text-slate-600">{{ $item->description }}</p> @endif
                <div class="mt-4 flex gap-4 border-t border-navy-100 pt-4 text-sm">
                    <x-button :href="route('admin.quotes.create')" variant="ghost" size="sm" class="-ml-3">New quote</x-button>
                    <form method="POST" action="{{ route('admin.inventory.destroy', $item) }}" data-confirm="Delete {{ $item->name }} and its stock history?" class="ml-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mt-2 font-semibold text-red-700 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </x-dash.card>
        </div>
    </div>
</x-layouts.dashboard>
