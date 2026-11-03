<x-layouts.dashboard title="Invoices" area="admin">
    <x-slot:subtitle>Bill customers, record payments and follow up on overdue invoices.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.invoices.export', array_filter(['status' => $filter]))" variant="outline" size="sm">Export CSV</x-button>
        <x-button :href="route('admin.invoices.create')" size="sm" ><span aria-hidden="true">+</span> New invoice</x-button>
    </x-slot:actions>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-dash.stat label="Outstanding" :value="\App\Support\Money::format($outstanding)" icon="clock" hint="Issued and not yet paid" :href="route('admin.invoices.index', ['status' => 'unpaid'])" />
        <x-dash.stat label="Overdue" :value="\App\Support\Money::format($overdue)" icon="mail" tone="signal" :hint="$counts['overdue'].' '.Str::plural('invoice', $counts['overdue']).' past the due date'" :href="route('admin.invoices.index', ['status' => 'overdue'])" />
        <x-dash.stat label="Received this month" :value="\App\Support\Money::format($paidThisMonth)" icon="check" tone="sky" :hint="now()->format('F Y')" />
    </div>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter invoices">
            <ul class="flex w-max gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                @foreach (['' => 'All'] + \App\Models\Invoice::FILTERS as $key => $label)
                    @php($active = $filter === $key || ($key === '' && ! array_key_exists($filter, \App\Models\Invoice::FILTERS)))
                    <li>
                        <a href="{{ route('admin.invoices.index', array_filter(['status' => $key, 'q' => $search])) }}" @if ($active) aria-current="page" @endif
                           @class(['inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition', 'bg-navy-800 text-white' => $active, 'text-slate-600 hover:text-navy-900' => ! $active])>
                            {{ $label }}
                            <span @class(['rounded-full px-1.5 font-mono text-xs', 'bg-signal-500 text-navy-950' => $active, 'bg-slate-100 text-slate-600' => ! $active])>{{ $counts[$key] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex gap-2" role="search">
            @if ($filter) <input type="hidden" name="status" value="{{ $filter }}"> @endif
            <label for="invoice-search" class="sr-only">Search invoices</label>
            <input id="invoice-search" type="search" name="q" value="{{ $search }}" placeholder="Number, customer, email…"
                   class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none lg:w-72">
            <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
        </form>
    </div>

    <x-dash.card :padded="false">
        @if ($invoices->isEmpty())
            <x-dash.empty icon="folder" :title="$search || $filter ? 'No invoices found' : 'No invoices yet'"
                          :text="$search || $filter ? 'Try a different filter or search.' : 'Create an invoice, or convert an accepted quote.'">
                @unless ($search || $filter)
                    <x-button :href="route('admin.invoices.create')" size="sm">Create an invoice</x-button>
                @endunless
            </x-dash.empty>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Invoice</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Due</th>
                            <th scope="col" class="px-6 py-3 text-right">Total</th>
                            <th scope="col" class="px-6 py-3 text-right">Balance</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($invoices as $invoice)
                            <tr @class(['transition hover:bg-slate-50', 'bg-red-50/40' => $invoice->isOverdue()])>
                                <td class="px-6 py-4"><a href="{{ route('admin.invoices.show', $invoice) }}" class="font-mono font-semibold text-navy-900 hover:text-signal-700">{{ $invoice->number }}</a></td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-navy-900">{{ $invoice->customer_name }}</p>
                                    @if ($invoice->customer_organization) <p class="text-slate-500">{{ $invoice->customer_organization }}</p> @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $invoice->issue_date->format('j M Y') }}</td>
                                <td @class(['px-6 py-4 whitespace-nowrap', 'font-semibold text-red-700' => $invoice->isOverdue(), 'text-slate-600' => ! $invoice->isOverdue()])>{{ $invoice->due_date?->format('j M Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ $invoice->money('total') }}</td>
                                <td class="px-6 py-4 text-right font-semibold whitespace-nowrap text-navy-900 tabular-nums">{{ $invoice->status === 'issued' ? \App\Support\Money::format($invoice->balance(), $invoice->currency) : '—' }}</td>
                                <td class="px-6 py-4"><x-admin.badge :state="$invoice->state()" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $invoices->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
