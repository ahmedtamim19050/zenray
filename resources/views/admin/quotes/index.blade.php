<x-layouts.dashboard title="Quotations" area="admin">
    <x-slot:subtitle>Open quotes worth <span class="font-semibold text-navy-900">{{ \App\Support\Money::format($openValue) }}</span> (sent or accepted).</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.quotes.create')" size="sm" ><span aria-hidden="true">+</span> New quote</x-button>
    </x-slot:actions>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter by status">
            <ul class="flex w-max gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                @foreach (['' => ['All', $counts->sum()]] + collect(\App\Models\Quote::STATUSES)->map(fn ($s, $k) => [$s[0], $counts[$k] ?? 0])->all() + ['expired' => ['Expired', null]] as $key => [$label, $count])
                    @php($active = $status === $key)
                    <li>
                        <a href="{{ route('admin.quotes.index', array_filter(['status' => $key, 'q' => $search])) }}" @if ($active) aria-current="page" @endif
                           @class(['inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition', 'bg-navy-800 text-white' => $active, 'text-slate-600 hover:text-navy-900' => ! $active])>
                            {{ $label }}
                            @if (! is_null($count))
                                <span @class(['rounded-full px-1.5 font-mono text-xs', 'bg-signal-500 text-navy-950' => $active, 'bg-slate-100 text-slate-600' => ! $active])>{{ $count }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="GET" action="{{ route('admin.quotes.index') }}" class="flex gap-2" role="search">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <label for="quote-search" class="sr-only">Search quotes</label>
            <input id="quote-search" type="search" name="q" value="{{ $search }}" placeholder="Number, customer, email…"
                   class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none lg:w-72">
            <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
        </form>
    </div>

    <x-dash.card :padded="false">
        @if ($quotes->isEmpty())
            <x-dash.empty icon="document" :title="$search || $status ? 'No quotes found' : 'No quotes yet'"
                          :text="$search || $status ? 'Try a different filter or search.' : 'Create a quotation for a customer, or start one from an enquiry.'">
                @unless ($search || $status)
                    <x-button :href="route('admin.quotes.create')" size="sm">Create a quote</x-button>
                @endunless
            </x-dash.empty>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Quote</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Valid until</th>
                            <th scope="col" class="px-6 py-3 text-right">Total</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($quotes as $quote)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4"><a href="{{ route('admin.quotes.show', $quote) }}" class="font-mono font-semibold text-navy-900 hover:text-signal-700">{{ $quote->number }}</a></td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-navy-900">{{ $quote->customer_name }}</p>
                                    @if ($quote->customer_organization) <p class="text-slate-500">{{ $quote->customer_organization }}</p> @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $quote->issue_date->format('j M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $quote->valid_until?->format('j M Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right font-semibold whitespace-nowrap text-navy-900 tabular-nums">{{ $quote->money('total') }}</td>
                                <td class="px-6 py-4"><x-admin.badge :state="$quote->state()" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($quotes->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $quotes->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
