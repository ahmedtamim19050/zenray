{{-- Standalone print / save-as-PDF view of a quote or invoice. --}}
@php($title = ($type === 'quote' ? 'Quotation ' : 'Invoice ').$document->number)

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['pageTitle' => $title.' | '.config('zenray.name'), 'description' => $title, 'noindex' => true])
        <style>
            @page { size: A4; margin: {{ $type === 'invoice' ? '0' : '12mm' }}; }
            @media print {
                html, body { background: white !important; }
                .no-print { display: none !important; }
                .doc-paper { box-shadow: none !important; max-width: none !important; overflow: visible !important; border-radius: 0 !important; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-slate-200 py-8 font-sans antialiased print:p-0">
        <div class="no-print mx-auto mb-6 flex max-w-[52rem] flex-wrap items-center justify-between gap-3 px-4">
            <a href="{{ $backUrl ?? route($type === 'quote' ? 'admin.quotes.show' : 'admin.invoices.show', $document) }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">← Back to {{ $type }}</a>
            @if ($padToggle ?? false)
                <nav aria-label="Paper type">
                    <ul class="flex gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                        @foreach (['Without pad' => false, 'With pad' => true] as $label => $isPadOption)
                            <li>
                                <a href="{{ route('admin.invoices.print', $isPadOption ? [$document, 'pad' => 1] : [$document]) }}" @if (($onPad ?? false) === $isPadOption) aria-current="page" @endif
                                   @class(['inline-flex rounded-full px-4 py-1.5 text-sm font-semibold whitespace-nowrap', 'bg-navy-800 text-white' => ($onPad ?? false) === $isPadOption, 'text-slate-600 hover:text-navy-900' => ($onPad ?? false) !== $isPadOption])>{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            <div class="flex items-center gap-3">
                <p class="hidden text-sm text-slate-600 sm:block">Choose “Save as PDF” in the print dialog to download.</p>
                <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-full bg-navy-800 px-5 text-sm font-semibold text-white hover:bg-navy-700">
                    <x-icon name="document" class="size-4" /> Print / Save PDF
                </button>
            </div>
        </div>

        @if ($onPad ?? false)
            <p class="no-print mx-auto mb-4 max-w-[52rem] px-4 text-sm text-slate-600">Load your pad in the printer. Header, footer and watermark are left blank because the pad already has them. Use A4 paper at 100% scale.</p>
        @endif

        <x-admin.document :document="$document" :type="$type" :on-pad="$onPad ?? false" class="rounded-lg shadow-xl" />
    </body>
</html>
