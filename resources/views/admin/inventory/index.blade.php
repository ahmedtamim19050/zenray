<x-layouts.dashboard title="Inventory" area="admin">
    <x-slot:subtitle>Stock on hand, value and reorder alerts. Issued invoices take stock out automatically.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.inventory.export', array_filter(['stock' => $filter, 'q' => $search]))" variant="outline" size="sm">Export CSV</x-button>
        <x-button :href="route('admin.inventory.create')" size="sm" class="btn-shine"><span aria-hidden="true">+</span> Add item</x-button>
    </x-slot:actions>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dash.stat label="Active items" :value="number_format($totals->items ?? 0)" icon="layers" :hint="number_format($totals->units ?? 0).' units in stock'" />
        <x-dash.stat label="Stock value (cost)" :value="\App\Support\Money::format($totals->cost_value ?? 0)" icon="folder" tone="slate" hint="Quantity × cost price" />
        <x-dash.stat label="Stock value (sale)" :value="\App\Support\Money::format($totals->sale_value ?? 0)" icon="check" tone="sky" :hint="'Potential margin '.\App\Support\Money::format(($totals->sale_value ?? 0) - ($totals->cost_value ?? 0))" />
        <x-dash.stat label="Low or out of stock" :value="$lowCount" icon="clock" tone="signal" hint="At or below the reorder level" :href="route('admin.inventory.index', ['stock' => 'low'])" />
    </div>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter stock">
            <ul class="flex w-max gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                @foreach (['' => 'All active'] + \App\Http\Controllers\Admin\InventoryController::FILTERS as $key => $label)
                    @php($active = $filter === $key)
                    <li>
                        <a href="{{ route('admin.inventory.index', array_filter(['stock' => $key, 'q' => $search])) }}" @if ($active) aria-current="page" @endif
                           @class(['inline-flex rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition', 'bg-navy-800 text-white' => $active, 'text-slate-600 hover:text-navy-900' => ! $active])>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex gap-2" role="search">
            @if ($filter) <input type="hidden" name="stock" value="{{ $filter }}"> @endif
            <label for="inventory-search" class="sr-only">Search inventory</label>
            <input id="inventory-search" type="search" name="q" value="{{ $search }}" placeholder="Name, SKU or location…"
                   class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none lg:w-72">
            <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
        </form>
    </div>

    <x-dash.card :padded="false">
        @if ($items->isEmpty())
            <x-dash.empty icon="layers" :title="$search || $filter ? 'No items found' : 'No inventory yet'"
                          :text="$search || $filter ? 'Try a different filter or search.' : 'Add the equipment, parts and accessories you stock.'">
                @unless ($search || $filter)
                    <x-button :href="route('admin.inventory.create')" size="sm">Add the first item</x-button>
                @endunless
            </x-dash.empty>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Item</th>
                            <th scope="col" class="px-6 py-3">Location</th>
                            <th scope="col" class="px-6 py-3 text-right">In stock</th>
                            <th scope="col" class="px-6 py-3 text-right">Cost</th>
                            <th scope="col" class="px-6 py-3 text-right">Price</th>
                            <th scope="col" class="px-6 py-3 text-right">Value</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($items as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.inventory.show', $item) }}" class="font-semibold text-navy-900 hover:text-signal-700">{{ $item->name }}</a>
                                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                        <span class="font-mono text-slate-500">{{ $item->sku }}</span>
                                        @if ($item->product_id)
                                            <span class="rounded-full bg-sky-50 px-2 py-0.5 font-semibold text-sky-700 ring-1 ring-sky-200 ring-inset" title="Shown on the website as a product">Website</span>
                                        @endif
                                        @if ($item->needsPricing())
                                            <a href="{{ route('admin.inventory.edit', $item) }}" class="rounded-full bg-signal-50 px-2 py-0.5 font-semibold text-signal-700 ring-1 ring-signal-200 ring-inset hover:bg-signal-100">Price not set</a>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $item->location ?: '—' }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums">
                                    <span @class(['font-semibold', 'text-red-700' => $item->quantity <= 0, 'text-signal-700' => $item->quantity > 0 && $item->isLow(), 'text-navy-900' => ! $item->isLow()])>{{ number_format($item->quantity) }}</span>
                                    <span class="text-slate-500">{{ $item->unit }}</span>
                                    @if ($item->reorder_level) <p class="text-xs text-slate-400">reorder at {{ $item->reorder_level }}</p> @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ \App\Support\Money::format($item->cost_price) }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ \App\Support\Money::format($item->sale_price) }}</td>
                                <td class="px-6 py-4 text-right font-semibold whitespace-nowrap text-navy-900 tabular-nums">{{ \App\Support\Money::format($item->stockValue()) }}</td>
                                <td class="px-6 py-4">
                                    @if ($item->is_active)
                                        <x-admin.badge :state="[$item->stockLabel(), $item->stockClasses()]" />
                                    @else
                                        <x-admin.badge :state="['Inactive', 'bg-slate-100 text-slate-500 ring-slate-200']" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $items->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
