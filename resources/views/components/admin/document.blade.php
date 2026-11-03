{{--
    A quotation or invoice laid out as a printable A4 document.
    Used on the admin detail pages and the print / save-as-PDF view.

    Invoices use a letterhead: header, footer and watermark on plain paper,
    or only the content (onPad) when printing on the pre-printed company pad.
    On the pad, the number and date line up with the pad's printed Ref and Date.
    When printing, the letterhead is fixed so it repeats on every page, and the
    outer table's empty head and foot keep the content clear of it.
--}}
@props(['document', 'type', 'onPad' => false])

@php
    $isQuote = $type === 'quote';
    $contact = config('zenray.contact');
    $billing = config('zenray.billing');
    $facebook = config('zenray.social.facebook');
    [$stateLabel] = $document->state();
    $invoiceDetails = $isQuote ? [] : array_filter([
        'Due date' => $document->due_date?->format('j M Y'),
        'Quote ref.' => $document->quote?->number,
        $billing['tax_label'].' no.' => $billing['tax_number'],
    ]);
    $stamp = match (true) {
        $document->status === 'void' => ['VOID', 'text-red-600 border-red-600'],
        $document->status === 'draft' => ['DRAFT', 'text-slate-400 border-slate-400'],
        default => null,
    };
@endphp

<article {{ $attributes->class(['doc-paper relative mx-auto w-full overflow-hidden bg-white text-[0.875rem] leading-relaxed text-slate-700', 'max-w-[52rem]' => $isQuote, 'flex max-w-[210mm] flex-col sm:min-h-[297mm] print:block print:min-h-0' => ! $isQuote]) }}>
    @if ($isQuote)
        <div class="h-2 bg-linear-to-r from-navy-800 via-navy-700 to-signal-500" aria-hidden="true"></div>
    @else
        {{-- Images rather than CSS backgrounds, because browsers skip backgrounds when printing. --}}
        @unless ($onPad)
            <div class="pointer-events-none absolute top-1/2 left-1/2 flex w-3/5 -translate-x-1/2 -translate-y-1/2 flex-col items-center select-none print:fixed" aria-hidden="true" data-watermark>
                <img src="{{ asset('images/zenray-mark.svg') }}" alt="" width="160" height="112" class="w-3/5 opacity-[0.12]">
                <p class="mt-2 text-center text-3xl font-bold tracking-wider whitespace-nowrap text-navy-800 uppercase opacity-[0.12]">{{ config('zenray.name') }}</p>
                <img src="{{ asset('images/invoice-watermark-equipment.png') }}" alt="" width="900" height="792" class="mt-2 w-full">
            </div>
        @endunless

        <div class="relative shrink-0 print:fixed print:inset-x-0 print:top-0">
            <div @class(['h-[24mm]' => $onPad, 'h-[32mm]' => ! $onPad])>
                @unless ($onPad)
                    <div class="flex h-full items-center justify-center gap-3 border-b border-navy-800 px-4 sm:gap-4" data-letterhead>
                        <img src="{{ asset('images/zenray-mark.svg') }}" alt="" width="160" height="112" class="h-[13mm] w-auto">
                        <div>
                            <p class="font-serif text-lg leading-tight font-bold tracking-wide text-navy-800 uppercase sm:text-3xl">{{ config('zenray.name') }}</p>
                            <p class="text-[0.6875rem] leading-tight font-medium text-slate-700 sm:text-xs">{{ config('zenray.tagline') }}</p>
                        </div>
                    </div>
                @endunless
            </div>
            <div class="relative h-[10mm] text-xs">
                @unless ($onPad)
                    <span class="absolute top-[3.5mm] left-[8%] text-slate-600 italic sm:left-[12%]">Ref:</span>
                    <span class="absolute top-[3.5mm] left-[69.5%] text-slate-600 italic">Date:</span>
                    <span class="absolute top-[7.5mm] left-[75%] w-[16%] border-b border-dotted border-slate-500"></span>
                @endunless
                <span class="absolute top-[3.5mm] left-[13%] font-mono font-semibold text-navy-900 sm:left-[16%]">{{ $document->number }}</span>
                <span class="absolute top-[3.5mm] left-[76%] font-semibold text-navy-900">{{ $document->issue_date->format('d/m/Y') }}</span>
            </div>
        </div>
    @endif

    @if ($stamp)
        <span class="pointer-events-none absolute {{ $isQuote ? 'top-40' : 'top-48' }} right-10 rotate-[-14deg] rounded-lg border-4 px-4 py-1 font-mono text-4xl font-bold tracking-widest opacity-25 {{ $stamp[1] }}" aria-hidden="true">{{ $stamp[0] }}</span>
    @endif

    @unless ($isQuote)
        <table class="relative w-full table-fixed" role="presentation">
            <thead class="hidden print:table-header-group"><tr><td @class(['h-[36mm]' => $onPad, 'h-[44mm]' => ! $onPad])></td></tr></thead>
            <tbody><tr><td>
    @endunless

    <div @class(['relative', 'p-8 sm:p-12' => $isQuote, 'px-6 py-4 sm:px-[7%] print:py-0' => ! $isQuote])>
        {{-- Header --}}
        @if ($isQuote)
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
                    <h1 class="text-3xl font-bold tracking-tight text-navy-900 uppercase">Quotation</h1>
                    <p class="mt-1 font-mono text-base font-semibold text-navy-700">{{ $document->number }}</p>
                    <dl class="mt-4 grid grid-cols-[auto_auto] justify-start gap-x-6 gap-y-1 text-xs sm:justify-end">
                        <dt class="text-slate-500">Date</dt><dd class="font-semibold text-navy-900">{{ $document->issue_date->format('j M Y') }}</dd>
                        @if ($document->valid_until)
                            <dt class="text-slate-500">Valid until</dt><dd class="font-semibold text-navy-900">{{ $document->valid_until->format('j M Y') }}</dd>
                        @endif
                        <dt class="text-slate-500">Status</dt><dd class="font-semibold text-navy-900">{{ $stateLabel }}</dd>
                    </dl>
                </div>
            </header>
        @else
            <header class="flex flex-wrap items-start justify-between gap-4">
                <h1 class="text-2xl font-bold tracking-tight text-navy-900 uppercase">Invoice</h1>
                <dl class="grid grid-cols-[auto_auto] gap-x-6 gap-y-1 text-xs">
                    @foreach ($invoiceDetails as $label => $value)
                        <dt class="text-slate-500">{{ $label }}</dt><dd class="font-semibold text-navy-900">{{ $value }}</dd>
                    @endforeach
                </dl>
            </header>
        @endif

        {{-- Parties --}}
        <div @class(['grid gap-6 rounded-xl bg-slate-50/60 p-5 sm:grid-cols-2', 'mt-10' => $isQuote, 'mt-5' => ! $isQuote])>
            <div>
                <p class="text-[0.6875rem] font-semibold tracking-widest text-slate-400 uppercase">{{ $isQuote ? 'Prepared for' : 'Bill to' }}</p>
                <p class="mt-1.5 font-bold text-navy-900">{{ $document->customer_name }}</p>
                @if ($document->customer_organization) <p>{{ $document->customer_organization }}</p> @endif
                @if ($document->customer_address) <p class="whitespace-pre-line">{{ $document->customer_address }}</p> @endif
                @if ($document->customer_email) <p>{{ $document->customer_email }}</p> @endif
                @if ($document->customer_phone) <p>{{ $document->customer_phone }}</p> @endif
            </div>
            @if ($isQuote)
                <div class="sm:text-right">
                    <p class="text-[0.6875rem] font-semibold tracking-widest text-slate-400 uppercase">Quotation total</p>
                    <p class="mt-1.5 text-3xl font-bold tracking-tight text-navy-900 tabular-nums">{{ $document->money('total') }}</p>
                    <p class="text-xs text-slate-500">{{ $document->currency }}</p>
                </div>
            @endif
        </div>

        {{-- Items --}}
        <table @class(['w-full text-left', 'mt-10' => $isQuote, 'mt-6' => ! $isQuote])>
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
                    <div class="flex justify-between font-semibold text-navy-900"><dt>Paid</dt><dd>{{ $document->money('amount_paid') }}</dd></div>
                    <div class="flex justify-between border-t-2 border-navy-800 pt-2 text-base font-bold text-navy-900"><dt>Amount due</dt><dd>{{ \App\Support\Money::format($document->balance(), $document->currency) }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Notes, terms, payment details --}}
        @if ($isQuote || $billing['payment_details'])
            <div class="mt-10 grid gap-6 border-t border-slate-200 pt-6 text-xs sm:grid-cols-2">
                @if ($isQuote && $document->notes)
                    <div>
                        <p class="font-semibold tracking-widest text-navy-800 uppercase">Notes</p>
                        <p class="mt-1.5 whitespace-pre-line">{{ $document->notes }}</p>
                    </div>
                @endif
                @if ($isQuote && $document->terms)
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
        @endif

        @unless ($isQuote)
            <div class="mt-20 flex justify-end break-inside-avoid" data-signature>
                <p class="w-56 border-t border-navy-800 pt-1.5 text-center text-xs font-semibold text-navy-900">Authorized signature</p>
            </div>
        @endunless

        @if ($isQuote)
            <footer class="mt-10 flex items-center justify-between gap-4 border-t border-slate-200 pt-4 text-[0.6875rem] text-slate-400">
                <p>{{ config('zenray.name') }} · {{ config('zenray.tagline') }}</p>
                <p class="font-mono">{{ $document->number }}</p>
            </footer>
        @endif
    </div>

    @unless ($isQuote)
            </td></tr></tbody>
            <tfoot class="hidden print:table-footer-group"><tr><td class="h-[28mm]"></td></tr></tfoot>
        </table>

        @unless ($onPad)
            <footer class="relative mt-auto grid min-h-[24mm] gap-2 bg-[#2E3192] px-[5%] py-3 text-xs leading-snug font-semibold text-white sm:grid-cols-2 print:fixed print:inset-x-0 print:bottom-0" data-letterhead>
                <div>
                    @if ($contact['address']) <p class="whitespace-pre-line">{{ $contact['address'] }}</p> @endif
                </div>
                <div class="space-y-0.5">
                    @if ($contact['phone']) <p>Mobile : {{ $contact['phone'] }}</p> @endif
                    @if ($contact['email']) <p>E-mail : {{ $contact['email'] }}</p> @endif
                    @if ($facebook) <p>Fb : {{ preg_replace('#^https?://#', '', $facebook) }}</p> @endif
                </div>
            </footer>
        @endunless
    @endunless
</article>
