<x-layouts.dashboard title="My invoices">
    <x-slot:subtitle>Invoices issued to your account, with what has been paid and what is still due.</x-slot:subtitle>

    <x-dash.card :padded="false">
        @if ($invoices->isEmpty())
            <x-dash.empty icon="folder" title="No invoices yet" text="Invoices from our team will be listed here once they are issued." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Invoice</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Due</th>
                            <th scope="col" class="px-6 py-3 text-right">Total</th>
                            <th scope="col" class="px-6 py-3 text-right">Balance</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($invoices as $invoice)
                            <tr @class(['transition hover:bg-slate-50', 'bg-red-50/40' => $invoice->isOverdue()])>
                                <td class="px-6 py-4"><a href="{{ route('dashboard.invoices.show', $invoice) }}" class="font-mono font-semibold text-navy-900 hover:text-signal-700">{{ $invoice->number }}</a></td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $invoice->issue_date->format('j M Y') }}</td>
                                <td @class(['px-6 py-4 whitespace-nowrap', 'font-semibold text-red-700' => $invoice->isOverdue(), 'text-slate-600' => ! $invoice->isOverdue()])>{{ $invoice->due_date?->format('j M Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-slate-600 tabular-nums">{{ $invoice->money('total') }}</td>
                                <td class="px-6 py-4 text-right font-semibold whitespace-nowrap text-navy-900 tabular-nums">{{ $invoice->status === 'issued' ? \App\Support\Money::format($invoice->balance(), $invoice->currency) : '—' }}</td>
                                <td class="px-6 py-4"><x-admin.badge :state="$invoice->state()" /></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('dashboard.invoices.show', $invoice) }}" class="font-semibold text-navy-800 hover:text-signal-700">View<span class="sr-only"> invoice {{ $invoice->number }}</span></a>
                                </td>
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
