@php
    use App\Support\Money;

    // Series colours validated with the dataviz palette checker (light surface).
    $series = ['invoiced' => ['Invoiced', '#2F6DB5'], 'received' => ['Payments received', '#D9741A']];
    $chartMax = max(1, $monthly->max(fn ($m) => max($m['invoiced'], $m['received'])));
    $agingMax = max(1, max($aging));
@endphp

<x-layouts.dashboard title="Reports" area="admin">
    <x-slot:subtitle>Sales, payments, quotations and stock at a glance. Figures use your default currency.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.invoices.export')" variant="outline" size="sm">Export invoices</x-button>
        <x-button :href="route('admin.inventory.export')" variant="outline" size="sm">Export inventory</x-button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-stagger>
        <x-dash.stat label="Received in {{ now()->year }}" :value="Money::format($summary['received_year'])" icon="check" tone="signal" hint="Payments recorded this year" />
        <x-dash.stat label="Invoiced in {{ now()->year }}" :value="Money::format($summary['invoiced_year'])" icon="document" tone="sky" hint="Issued invoices this year" />
        <x-dash.stat label="Outstanding" :value="Money::format($summary['outstanding'])" icon="clock" hint="Owed on issued invoices" :href="route('admin.invoices.index', ['status' => 'unpaid'])" />
        <x-dash.stat label="Average invoice" :value="Money::format($summary['average_invoice'])" icon="layers" tone="slate" hint="Across all issued invoices" />
    </div>

    {{-- Monthly invoiced vs received: grouped bars, one axis, legend + hover tooltips + table view --}}
    <x-dash.card title="Last 12 months" description="Invoiced amount and payments received per month" class="mt-6">
        <x-slot:actions>
            <ul class="flex flex-wrap gap-4 text-sm text-slate-600" aria-label="Legend">
                @foreach ($series as [$label, $color])
                    <li class="flex items-center gap-2"><span class="size-3 rounded-sm" style="background: {{ $color }}" aria-hidden="true"></span>{{ $label }}</li>
                @endforeach
            </ul>
        </x-slot:actions>

        <div class="flex gap-3">
            <div class="flex h-64 flex-col justify-between pb-6 text-right text-xs text-slate-400 tabular-nums" aria-hidden="true">
                <span>{{ Money::format($chartMax) }}</span>
                <span>{{ Money::format($chartMax / 2) }}</span>
                <span>{{ Money::format(0) }}</span>
            </div>
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-x-0 top-0 bottom-6 flex flex-col justify-between" aria-hidden="true">
                    <span class="border-t border-dashed border-slate-200"></span>
                    <span class="border-t border-dashed border-slate-200"></span>
                    <span class="border-t border-slate-300"></span>
                </div>
                <ol class="relative grid h-64 grid-cols-12 gap-1 sm:gap-2" aria-label="Monthly totals">
                    @foreach ($monthly as $month)
                        <li class="group relative flex flex-col justify-end">
                            <div class="flex h-full items-end justify-center gap-0.5 pb-6">
                                @foreach ($series as $key => [$label, $color])
                                    <span class="block w-full max-w-5 rounded-t-[4px] transition-opacity group-hover:opacity-90"
                                          style="height: {{ $month[$key] > 0 ? max(1.5, $month[$key] / $chartMax * 100) : 0 }}%; background: {{ $color }}"></span>
                                @endforeach
                            </div>
                            <span class="absolute inset-x-0 bottom-0 text-center text-[0.6875rem] text-slate-500">{{ $month['label'] }}</span>
                            {{-- Tooltip --}}
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden w-max -translate-x-1/2 rounded-lg bg-navy-950 px-3 py-2 text-xs text-white shadow-lg group-hover:block" role="tooltip">
                                <p class="font-semibold">{{ $month['label'] }}</p>
                                @foreach ($series as $key => [$label, $color])
                                    <p class="mt-0.5 flex items-center gap-2"><span class="size-2 rounded-sm" style="background: {{ $color }}"></span>{{ $label }}: <span class="font-semibold tabular-nums">{{ Money::format($month[$key]) }}</span></p>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <details class="mt-4 text-sm">
            <summary class="cursor-pointer font-semibold text-navy-800">Show as table</summary>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs text-slate-500 uppercase"><tr><th scope="col" class="py-2">Month</th><th scope="col" class="py-2 text-right">Invoiced</th><th scope="col" class="py-2 text-right">Received</th></tr></thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($monthly as $month)
                            <tr><td class="py-1.5">{{ $month['label'] }}</td><td class="py-1.5 text-right tabular-nums">{{ Money::format($month['invoiced']) }}</td><td class="py-1.5 text-right tabular-nums">{{ Money::format($month['received']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </x-dash.card>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-dash.card title="Money owed by age" description="Balance of issued invoices, by days past due">
            <ul class="space-y-3.5">
                @foreach ($aging as $label => $amount)
                    <li title="{{ $label }}: {{ Money::format($amount) }}">
                        <span class="flex justify-between text-sm"><span class="text-slate-600">{{ $label }}</span><span class="font-semibold text-navy-900 tabular-nums">{{ Money::format($amount) }}</span></span>
                        <span class="mt-1.5 block h-2 rounded-full bg-slate-100">
                            <span class="block h-2 rounded-full" style="width: {{ $amount > 0 ? max(2, round($amount / $agingMax * 100)) : 0 }}%; background: {{ $loop->first ? '#2F6DB5' : '#D9741A' }}"></span>
                        </span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 text-xs text-slate-500">Blue is not yet due; orange is overdue.</p>
        </x-dash.card>

        <x-dash.card title="Quotations" description="How quotes are progressing">
            <p class="text-sm text-slate-500">Win rate</p>
            <p class="text-4xl font-bold tracking-tight text-navy-900 tabular-nums">{{ is_null($quotes['win_rate']) ? '—' : $quotes['win_rate'].'%' }}</p>
            <p class="text-xs text-slate-500">Accepted or invoiced, out of all decided quotes</p>
            <dl class="mt-5 divide-y divide-navy-50 text-sm">
                @foreach ($quotes['counts'] as $key => $row)
                    <div class="flex items-center justify-between gap-3 py-2">
                        <dt><a href="{{ route('admin.quotes.index', ['status' => $key]) }}" class="text-slate-600 hover:text-navy-900">{{ $row['label'] }}</a></dt>
                        <dd class="text-right tabular-nums"><span class="font-semibold text-navy-900">{{ $row['count'] }}</span> <span class="text-slate-500">· {{ Money::format($row['value']) }}</span></dd>
                    </div>
                @endforeach
            </dl>
        </x-dash.card>

        <x-dash.card title="Inventory" description="Current stock value">
            <dl class="grid grid-cols-2 gap-4">
                <div><dt class="text-sm text-slate-500">At cost</dt><dd class="text-2xl font-bold text-navy-900 tabular-nums">{{ Money::format($inventory['cost_value']) }}</dd></div>
                <div><dt class="text-sm text-slate-500">At sale price</dt><dd class="text-2xl font-bold text-navy-900 tabular-nums">{{ Money::format($inventory['sale_value']) }}</dd></div>
            </dl>
            <p class="mt-5 text-sm font-semibold text-navy-800">Needs reordering</p>
            @forelse ($inventory['low'] as $item)
                <a href="{{ route('admin.inventory.show', $item) }}" class="mt-2 flex items-center justify-between gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50">
                    <span class="truncate text-navy-900">{{ $item->name }}</span>
                    <x-admin.badge :state="[$item->quantity.' left', $item->stockClasses()]" />
                </a>
            @empty
                <p class="mt-2 text-sm text-slate-500">Nothing is below its reorder level.</p>
            @endforelse
        </x-dash.card>
    </div>

    <x-dash.card title="Top selling items" description="By revenue on issued invoices" class="mt-6" :padded="false">
        @if ($topItems->isEmpty())
            <x-dash.empty icon="layers" title="No sales yet" text="Items from issued invoices will be ranked here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr><th scope="col" class="px-6 py-3">#</th><th scope="col" class="px-6 py-3">Item</th><th scope="col" class="px-6 py-3 text-right">Quantity</th><th scope="col" class="px-6 py-3 text-right">Revenue</th></tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($topItems as $row)
                            <tr>
                                <td class="px-6 py-3 text-slate-400 tabular-nums">{{ $loop->iteration }}</td>
                                <td class="px-6 py-3 text-navy-900">{{ Str::limit(Str::before($row->description, "\n"), 80) }}</td>
                                <td class="px-6 py-3 text-right tabular-nums">{{ rtrim(rtrim(number_format((float) $row->quantity, 2), '0'), '.') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-navy-900 tabular-nums">{{ Money::format($row->revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dash.card>
</x-layouts.dashboard>
