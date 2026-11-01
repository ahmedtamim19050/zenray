<x-layouts.dashboard :title="'Invoice '.$invoice->number">
    <x-slot:subtitle>Issued {{ $invoice->issue_date->format('j F Y') }} · {{ $invoice->money('total') }}</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('dashboard.invoices.index')" variant="outline" size="sm">← All invoices</x-button>
        <x-button :href="route('dashboard.invoices.print', $invoice)" variant="navy" size="sm" target="_blank" rel="noopener"><x-icon name="document" class="size-4" /> Print / PDF</x-button>
    </x-slot:actions>

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-admin.document :document="$invoice" type="invoice" class="rounded-2xl shadow-card ring-1 ring-navy-100" />
        </div>

        <div class="grid gap-6 xl:sticky xl:top-24">
            <x-dash.card title="Status">
                <div class="flex items-center justify-between gap-3">
                    <x-admin.badge :state="$invoice->state()" />
                    @if ($invoice->status === 'issued')
                        <span class="text-right text-sm"><span class="block text-slate-500">Balance due</span><span class="text-lg font-bold text-navy-900 tabular-nums">{{ \App\Support\Money::format($invoice->balance(), $invoice->currency) }}</span></span>
                    @endif
                </div>
                @if ($invoice->status === 'void')
                    <p class="mt-4 text-sm text-slate-600">This invoice has been cancelled. Nothing is due on it.</p>
                @endif
            </x-dash.card>

            <x-dash.card title="Invoice details">
                <dl class="space-y-2 text-sm">
                    @foreach (array_filter([
                        'Invoice number' => $invoice->number,
                        'Invoice date' => $invoice->issue_date->format('j M Y'),
                        'Due date' => $invoice->due_date?->format('j M Y'),
                        'Quote ref.' => $invoice->quote?->number,
                        'Items' => $invoice->items->count(),
                        'Total' => $invoice->money('total'),
                        'Paid' => $invoice->money('amount_paid'),
                        'Paid in full' => $invoice->paid_at?->format('j M Y'),
                        'Cancelled' => $invoice->voided_at?->format('j M Y'),
                    ]) as $label => $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right font-medium text-navy-900 tabular-nums">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </x-dash.card>

            @if ($invoice->status === 'issued')
                <x-dash.card title="Payments">
                    @forelse ($invoice->payments as $payment)
                        <div class="border-b border-navy-50 py-2.5 text-sm first:pt-0 last:border-0">
                            <p class="font-semibold text-navy-900 tabular-nums">{{ \App\Support\Money::format($payment->amount, $invoice->currency) }}</p>
                            <p class="text-slate-500">{{ $payment->paid_on->format('j M Y') }} · {{ $payment->methodLabel() }}@if ($payment->reference) · {{ $payment->reference }}@endif</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No payments recorded yet.</p>
                    @endforelse
                </x-dash.card>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
