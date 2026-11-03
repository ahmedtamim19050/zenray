@php
    $input = 'block w-full rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-navy-900 shadow-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none';
    $balance = \App\Support\Money::format($invoice->balance(), $invoice->currency);
@endphp

<x-layouts.dashboard :title="'Invoice '.$invoice->number" area="admin">
    <x-slot:subtitle>
        <a href="{{ route('admin.invoices.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← All invoices</a>
        <span class="text-slate-500">· {{ $invoice->customer_name }} · {{ $invoice->money('total') }}</span>
    </x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.invoices.print', $invoice)" variant="outline" size="sm" target="_blank" rel="noopener"><x-icon name="document" class="size-4" /> Print / PDF</x-button>
        <x-button :href="route('admin.invoices.print', [$invoice, 'pad' => 1])" variant="outline" size="sm" target="_blank" rel="noopener"><x-icon name="document" class="size-4" /> Print on pad</x-button>
        @if ($invoice->isEditable())
            <x-button :href="route('admin.invoices.edit', $invoice)" variant="navy" size="sm">Edit invoice</x-button>
        @endif
    </x-slot:actions>

    @if ($errors->hasAny(['stock', 'void', 'invoice']))
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">{{ $errors->first('stock') ?: $errors->first('void') ?: $errors->first('invoice') }}</div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-admin.document :document="$invoice" type="invoice" class="rounded-2xl shadow-card ring-1 ring-navy-100" />
        </div>

        <div class="grid gap-6 xl:sticky xl:top-24">
            <x-dash.card title="Status">
                <div class="flex items-center justify-between gap-3">
                    <x-admin.badge :state="$invoice->state()" />
                    @if ($invoice->status === 'issued')
                        <span class="text-right text-sm"><span class="block text-slate-500">Balance due</span><span class="text-lg font-bold text-navy-900 tabular-nums">{{ $balance }}</span></span>
                    @endif
                </div>

                @if ($invoice->status === 'draft')
                    <p class="mt-4 text-sm text-slate-600">This invoice is a draft. Issuing it locks it for editing and takes stocked items out of inventory.</p>
                    <form method="POST" action="{{ route('admin.invoices.issue', $invoice) }}" class="mt-4" data-confirm="Issue invoice {{ $invoice->number }}? It can no longer be edited afterwards.">
                        @csrf
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-signal-500 px-5 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                            Issue invoice <x-icon name="arrow-right" class="size-4" />
                        </button>
                    </form>
                @endif

                <dl class="mt-5 space-y-2 border-t border-navy-100 pt-4 text-sm">
                    @foreach (array_filter([
                        'Issued' => $invoice->issued_at?->format('j M Y, H:i'),
                        'Paid in full' => $invoice->paid_at?->format('j M Y'),
                        'Voided' => $invoice->voided_at?->format('j M Y, H:i'),
                        'Created by' => $invoice->creator?->name,
                    ]) as $label => $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right text-navy-900">{{ $value }}</dd></div>
                    @endforeach
                    @if ($invoice->quote)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">From quote</dt><dd><a href="{{ route('admin.quotes.show', $invoice->quote) }}" class="font-semibold text-navy-800 hover:text-signal-700">{{ $invoice->quote->number }}</a></dd></div>
                    @endif
                    @if ($invoice->customer)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer account</dt><dd><a href="{{ route('admin.customers.show', $invoice->customer) }}" class="font-semibold text-navy-800 hover:text-signal-700">{{ $invoice->customer->name }}</a></dd></div>
                    @endif
                </dl>
            </x-dash.card>

            @if ($invoice->status === 'issued')
                <x-dash.card title="Payments">
                    @forelse ($invoice->payments as $payment)
                        <div class="flex items-start justify-between gap-3 border-b border-navy-50 py-2.5 text-sm first:pt-0 last:border-0">
                            <div>
                                <p class="font-semibold text-navy-900 tabular-nums">{{ \App\Support\Money::format($payment->amount, $invoice->currency) }}</p>
                                <p class="text-slate-500">{{ $payment->paid_on->format('j M Y') }} · {{ $payment->methodLabel() }}@if ($payment->reference) · {{ $payment->reference }}@endif</p>
                            </div>
                            <form method="POST" action="{{ route('admin.invoices.payments.destroy', [$invoice, $payment]) }}" data-confirm="Remove this payment?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-700 hover:text-red-800">Remove<span class="sr-only"> payment of {{ $payment->amount }}</span></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No payments recorded yet.</p>
                    @endforelse

                    @unless ($invoice->isPaid())
                        <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="mt-5 grid gap-3 border-t border-navy-100 pt-5" novalidate>
                            @csrf
                            <p class="text-sm font-semibold text-navy-800">Record a payment</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="pay-amount" class="mb-1 block text-xs font-semibold text-slate-500">Amount</label>
                                    <input type="number" id="pay-amount" name="amount" value="{{ old('amount', $invoice->balance()) }}" min="0.01" step="0.01" required class="{{ $input }} tabular-nums" @error('amount') aria-invalid="true" @enderror>
                                </div>
                                <div>
                                    <label for="pay-date" class="mb-1 block text-xs font-semibold text-slate-500">Date</label>
                                    <input type="date" id="pay-date" name="paid_on" value="{{ old('paid_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $input }}">
                                </div>
                            </div>
                            <div>
                                <label for="pay-method" class="mb-1 block text-xs font-semibold text-slate-500">Method</label>
                                <select id="pay-method" name="method" class="{{ $input }}">
                                    @foreach (\App\Models\Payment::METHODS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('method') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="pay-ref" class="mb-1 block text-xs font-semibold text-slate-500">Reference (optional)</label>
                                <input type="text" id="pay-ref" name="reference" value="{{ old('reference') }}" maxlength="120" class="{{ $input }}" placeholder="Transaction or cheque number">
                            </div>
                            @foreach (['amount', 'paid_on', 'method', 'reference'] as $field)
                                @error($field) <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                            @endforeach
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-full bg-navy-800 px-5 text-sm font-semibold text-white hover:bg-navy-700">Record payment</button>
                        </form>
                    @endunless
                </x-dash.card>
            @endif

            <x-dash.card title="Send & manage">
                @if ($invoice->customer_email && $invoice->status !== 'void')
                    @php
                        $due = $invoice->due_date ? ', due by '.$invoice->due_date->format('j F Y') : '';
                        $mailto = 'mailto:'.$invoice->customer_email
                            .'?subject='.rawurlencode('Invoice '.$invoice->number.' from '.config('zenray.name'))
                            .'&body='.rawurlencode("Dear {$invoice->customer_name},\n\nPlease find invoice {$invoice->number} attached. Amount due: {$balance}{$due}.\n\nKind regards,\n".config('zenray.name'));
                    @endphp
                    <x-button :href="$mailto" variant="outline" size="sm" class="w-full"><x-icon name="mail" class="size-4" /> Email {{ $invoice->customer_email }}</x-button>
                    <p class="mt-2 text-xs text-slate-500">Opens your email app. Save the PDF from “Print / PDF” and attach it.</p>
                @endif

                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-navy-100 pt-4 text-sm">
                    @if ($invoice->status === 'draft')
                        <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" data-confirm="Delete draft invoice {{ $invoice->number }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-red-700 hover:text-red-800">Delete draft</button>
                        </form>
                    @elseif ($invoice->status === 'issued')
                        <form method="POST" action="{{ route('admin.invoices.void', $invoice) }}" data-confirm="Void invoice {{ $invoice->number }}? Sold stock will be returned to inventory. This cannot be undone.">
                            @csrf
                            <button type="submit" class="font-semibold text-red-700 hover:text-red-800">Void invoice</button>
                        </form>
                    @else
                        <p class="text-slate-500">This invoice has been voided and is kept for your records.</p>
                    @endif
                </div>
            </x-dash.card>
        </div>
    </div>
</x-layouts.dashboard>
