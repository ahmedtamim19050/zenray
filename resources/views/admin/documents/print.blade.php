{{-- Standalone print / save-as-PDF view of a quote or invoice. --}}
@php($title = ($type === 'quote' ? 'Quotation ' : 'Invoice ').$document->number)

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['pageTitle' => $title.' | '.config('zenray.name'), 'description' => $title, 'noindex' => true])
        <style>
            @page { size: A4; margin: 12mm; }
            @media print {
                html, body { background: white !important; }
                .no-print { display: none !important; }
                .doc-paper { box-shadow: none !important; max-width: none !important; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-slate-200 py-8 font-sans antialiased print:p-0">
        <div class="no-print mx-auto mb-6 flex max-w-[52rem] flex-wrap items-center justify-between gap-3 px-4">
            <a href="{{ route($type === 'quote' ? 'admin.quotes.show' : 'admin.invoices.show', $document) }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">← Back to {{ $type }}</a>
            <div class="flex items-center gap-3">
                <p class="hidden text-sm text-slate-600 sm:block">Choose “Save as PDF” in the print dialog to download.</p>
                <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-full bg-navy-800 px-5 text-sm font-semibold text-white hover:bg-navy-700">
                    <x-icon name="document" class="size-4" /> Print / Save PDF
                </button>
            </div>
        </div>

        <x-admin.document :document="$document" :type="$type" class="rounded-lg shadow-xl" />
    </body>
</html>
