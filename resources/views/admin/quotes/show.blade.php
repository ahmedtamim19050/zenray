<x-layouts.dashboard :title="'Quote '.$quote->number" area="admin">
    <x-slot:subtitle>
        <a href="{{ route('admin.quotes.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← All quotes</a>
        <span class="text-slate-500">· {{ $quote->customer_name }} · {{ $quote->money('total') }}</span>
    </x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.quotes.print', $quote)" variant="outline" size="sm" target="_blank" rel="noopener"><x-icon name="document" class="size-4" /> Print / PDF</x-button>
        @if ($quote->isEditable())
            <x-button :href="route('admin.quotes.edit', $quote)" variant="navy" size="sm">Edit quote</x-button>
        @endif
    </x-slot:actions>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-admin.document :document="$quote" type="quote" class="rounded-2xl shadow-card ring-1 ring-navy-100" />
        </div>

        <div class="grid gap-6 xl:sticky xl:top-24">
            <x-dash.card title="Status">
                <div class="flex items-center justify-between">
                    <x-admin.badge :state="$quote->state()" />
                    @if ($quote->sent_at) <span class="text-xs text-slate-500">Sent {{ $quote->sent_at->format('j M Y') }}</span> @endif
                </div>

                @if ($quote->invoice)
                    <a href="{{ route('admin.invoices.show', $quote->invoice) }}" class="mt-5 flex items-center justify-between rounded-xl bg-violet-50 p-4 text-sm ring-1 ring-violet-200 hover:ring-violet-300">
                        <span><span class="block font-semibold text-navy-900">Invoice {{ $quote->invoice->number }}</span><span class="text-slate-600">{{ $quote->invoice->state()[0] }}</span></span>
                        <x-icon name="arrow-right" class="size-4 text-violet-700" />
                    </a>
                @else
                    <form method="POST" action="{{ route('admin.quotes.status', $quote) }}" class="mt-5">
                        @csrf
                        @method('PATCH')
                        <p class="mb-2 text-sm font-semibold text-navy-800">Mark as</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['sent' => 'Sent', 'accepted' => 'Accepted', 'declined' => 'Declined', 'draft' => 'Draft'] as $key => $label)
                                <button type="submit" name="status" value="{{ $key }}" @disabled($quote->status === $key)
                                        class="rounded-xl px-3 py-2 text-sm font-semibold ring-1 transition disabled:cursor-default disabled:bg-navy-800 disabled:text-white disabled:ring-navy-800 enabled:text-slate-700 enabled:ring-navy-200 enabled:hover:bg-slate-50">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-signal-500 px-5 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                            Convert to invoice <x-icon name="arrow-right" class="size-4" />
                        </button>
                    </form>
                @endif
            </x-dash.card>

            <x-dash.card title="Send to customer">
                @if ($quote->customer_email)
                    @php
                        $mailto = 'mailto:'.$quote->customer_email
                            .'?subject='.rawurlencode('Quotation '.$quote->number.' from '.config('zenray.name'))
                            .'&body='.rawurlencode("Dear {$quote->customer_name},\n\nPlease find our quotation {$quote->number} attached, for a total of {$quote->money('total')}.\n\nKind regards,\n".config('zenray.name'));
                    @endphp
                    <x-button :href="$mailto" variant="outline" size="sm" class="w-full"><x-icon name="mail" class="size-4" /> Email {{ $quote->customer_email }}</x-button>
                    <p class="mt-2 text-xs text-slate-500">Opens your email app. Save the PDF from “Print / PDF” and attach it, then mark the quote as sent.</p>
                @else
                    <p class="text-sm text-slate-500">Add a customer email to send this quote by email.</p>
                @endif
            </x-dash.card>

            <x-dash.card title="Details">
                <dl class="space-y-2.5 text-sm">
                    @foreach (array_filter([
                        'Created by' => $quote->creator?->name,
                        'Created' => $quote->created_at->format('j M Y, H:i'),
                        'Customer account' => $quote->customer ? new \Illuminate\Support\HtmlString('<a class="font-semibold text-navy-800 hover:text-signal-700" href="'.e(route('admin.customers.show', $quote->customer)).'">'.e($quote->customer->name).'</a>') : null,
                        'Enquiry' => $quote->enquiry ? new \Illuminate\Support\HtmlString('<a class="font-semibold text-navy-800 hover:text-signal-700" href="'.e(route('admin.enquiries.show', $quote->enquiry)).'">#'.str_pad($quote->enquiry->id, 5, '0', STR_PAD_LEFT).'</a>') : null,
                    ]) as $label => $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right text-navy-900">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-navy-100 pt-4 text-sm">
                    <form method="POST" action="{{ route('admin.quotes.duplicate', $quote) }}">
                        @csrf
                        <button type="submit" class="font-semibold text-navy-800 hover:text-signal-700">Duplicate</button>
                    </form>
                    @unless ($quote->invoice)
                        <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" data-confirm="Delete quote {{ $quote->number }}? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-red-700 hover:text-red-800">Delete</button>
                        </form>
                    @endunless
                </div>
            </x-dash.card>
        </div>
    </div>
</x-layouts.dashboard>
