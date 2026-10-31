{{--
    A quotation or invoice laid out as a printable A4 document.
    Used on the admin detail pages and the print / save-as-PDF view.
--}}
@props(['document', 'type'])

@php
    $isQuote = $type === 'quote';
    $contact = config('zenray.contact');
    $billing = config('zenray.billing');
    [$stateLabel] = $document->state();
    $stamp = match (true) {
        ! $isQuote && $document->isPaid() => ['PAID', 'text-emerald-600 border-emerald-600'],
        $document->status === 'void' => ['VOID', 'text-red-600 border-red-600'],
        $document->status === 'draft' => ['DRAFT', 'text-slate-400 border-slate-400'],
        default => null,
    };
@endphp

<article {{ $attributes->class('doc-paper relative mx-auto w-full max-w-[52rem] overflow-hidden bg-white text-[0.875rem] leading-relaxed text-slate-700') }}>
    <div class="h-2 bg-linear-to-r from-navy-800 via-navy-700 to-signal-500" aria-hidden="true"></div>

    @if ($stamp)
        <span class="pointer-events-none absolute top-40 right-10 rotate-[-14deg] rounded-lg border-4 px-4 py-1 font-mono text-4xl font-bold tracking-widest opacity-25 {{ $stamp[1] }}" aria-hidden="true">{{ $stamp[0] }}</span>
    @endif

    <div class="p-8 sm:p-12">
        {{-- Header --}}
        <header class="flex flex-col justify-between gap-8 sm:flex-row">
            <div>
                <img src="{{ asset('images/zenray-logo.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" class="h-12 w-auto">
                <div class="mt-5 space-y-0.5 text-xs text-slate-500">
                    @if ($contact['address']) <p class="whitespace-pre-line">{{ $contact['address'] }}</p> @endif
                    @if ($contact['phone']) <p>{{ $contact['phone'] }}</p> @endif
                    @if ($contact['email']) <p>{{ $contact['email'] }}</p> @endif
                    @if ($billing['tax_number']) <p>{{ $billing['tax_label'] }} no. {{ $billing['tax_number'] }}</p> @endif
                </div>
            </div>
            <div class="sm:text-right">
                <h1 class="text-3xl font-bold tracking-tight text-navy-900 uppercase">{{ $isQuote ? 'Quotation' : 'Invoice' }}</h1>
                <p class="mt-1 font-mono text-base font-semibold text-navy-700">{{ $document->number }}</p>
                <dl class="mt-4 grid grid-cols-[auto_auto] justify-start gap-x-6 gap-y-1 text-xs sm:justify-end">
                    <dt class="text-slate-500">Date</dt><dd class="font-semibold text-navy-900">{{ $document->issue_date->format('j M Y') }}</dd>
                    @if ($isQuote && $document->valid_until)
                        <dt class="text-slate-500">Valid until</dt><dd class="font-semibold text-navy-900">{{ $document->valid_until->format('j M Y') }}</dd>
                    @elseif (! $isQuote && $document->due_date)
                        <dt class="text-slate-500">Due date</dt><dd class="font-semibold text-navy-900">{{ $document->due_date->format('j M Y') }}</dd>
                    @endif
                    @if (! $isQuote && $document->quote)
                        <dt class="text-slate-500">Quote ref.</dt><dd class="font-semibold text-navy-900">{{ $document->quote->number }}</dd>
                    @endif
                    <dt class="text-slate-500">Status</dt><dd class="font-semibold text-navy-900">{{ $stateLabel }}</dd>
                </dl>
            </div>
        </header>

        {{-- Parties --}}
        <div class="mt-10 grid gap-6 rounded-xl bg-slate-50 p-5 sm:grid-cols-2">
            <div>
                <p class="text-[0.6875rem] font-semibold tracking-widest text-slate-400 uppercase">{{ $isQuote ? 'Prepared for' : 'Bill to' }}</p>
                <p class="mt-1.5 font-bold text-navy-900">{{ $document->customer_name }}</p>
                @if ($document->customer_organization) <p>{{ $document->customer_organization }}</p> @endif
                @if ($document->customer_address) <p class="whitespace-pre-line">{{ $document->customer_address }}</p> @endif
                @if ($document->customer_email) <p>{{ $document->customer_email }}</p> @endif
                @if ($document->customer_phone) <p>{{ $document->customer_phone }}</p> @endif
            </div>
            <div class="sm:text-right">
                <p class="text-[0.6875rem] font-semibold tracking-widest text-slate-400 uppercase">{{ $isQuote ? 'Quotation total' : ($document->status === 'issued' ? 'Balance due' : 'Amount') }}</p>
                <p class="mt-1.5 text-3xl font-bold tracking-tight text-navy-900 tabular-nums">
                    {{ ! $isQuote && $document->status === 'issued' ? \App\Support\Money::format($document->balance(), $document->currency) : $document->money('total') }}
                </p>
                <p class="text-xs text-slate-500">{{ $document->currency }}</p>
            </div>
        </div>

        {{-- Items --}}
        <table class="mt-10 w-full text-left">
            <thead>
                <tr class="border-b-2 border-navy-800 text-[0.6875rem] font-semibold tracking-widest text-navy-800 uppercase">
                    <th scope="col" class="w-8 py-2 pr-2">#</th>
                    <th scope="col" class="py-2 pr-4">Description</th>
                    <th scope="col" class="py-2 pr-4 text-right">Qty</th>
                    <th scope="col" class="py-2 pr-4 text-right">Unit price</th>
                    <th scope="col" class="py-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->items as $item)
                    <tr class="border-b border-slate-200 align-top">
                        <td class="py-3 pr-2 text-slate-400 tabular-nums">{{ $loop->iteration }}</td>
                        <td class="py-3 pr-4 whitespace-pre-line text-navy-900">{{ $item->description }}</td>
                        <td class="py-3 pr-4 text-right whitespace-nowrap tabular-nums">{{ $item->quantityLabel() }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
                        <td class="py-3 pr-4 text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($item->unit_price, $document->currency) }}</td>
                        <td class="py-3 text-right font-semibold whitespace-nowrap text-navy-900 tabular-nums">{{ \App\Support\Money::format($item->line_total, $document->currency) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="mt-6 flex justify-end">
            <dl class="w-full max-w-xs space-y-1.5 tabular-nums">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ $document->money('subtotal') }}</dd></div>
                @if ((float) $document->discount > 0)
                    <div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd>-{{ $document->money('discount') }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-slate-500">{{ $billing['tax_label'] }} ({{ (float) $document->tax_rate }}%)</dt><dd>{{ $document->money('tax_total') }}</dd></div>
                <div class="flex justify-between border-t-2 border-navy-800 pt-2 text-base font-bold text-navy-900"><dt>Total</dt><dd>{{ $document->money('total') }}</dd></div>
                @if (! $isQuote && (float) $document->amount_paid > 0)
                    <div class="flex justify-between text-emerald-700"><dt>Paid</dt><dd>-{{ $document->money('amount_paid') }}</dd></div>
                    <div class="flex justify-between font-bold text-navy-900"><dt>Balance due</dt><dd>{{ \App\Support\Money::format($document->balance(), $document->currency) }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Notes, terms, payment details --}}
        <div class="mt-10 grid gap-6 border-t border-slate-200 pt-6 text-xs sm:grid-cols-2">
            @if ($document->notes)
                <div>
                    <p class="font-semibold tracking-widest text-navy-800 uppercase">Notes</p>
                    <p class="mt-1.5 whitespace-pre-line">{{ $document->notes }}</p>
                </div>
            @endif
            @if ($document->terms)
                <div>
                    <p class="font-semibold tracking-widest text-navy-800 uppercase">Terms</p>
                    <p class="mt-1.5 whitespace-pre-line">{{ $document->terms }}</p>
                </div>
            @endif
            @if (! $isQuote && $billing['payment_details'])
                <div class="sm:col-span-2">
                    <p class="font-semibold tracking-widest text-navy-800 uppercase">How to pay</p>
                    <p class="mt-1.5 whitespace-pre-line">{{ $billing['payment_details'] }}</p>
                    <p class="mt-1 text-slate-500">Please quote <span class="font-mono font-semibold text-navy-900">{{ $document->number }}</span> as the payment reference.</p>
                </div>
            @endif
        </div>

        <footer class="mt-10 flex items-center justify-between gap-4 border-t border-slate-200 pt-4 text-[0.6875rem] text-slate-400">
            <p>{{ config('zenray.name') }} · {{ config('zenray.tagline') }}</p>
            <p class="font-mono">{{ $document->number }}</p>
        </footer>
    </div>
</article>
