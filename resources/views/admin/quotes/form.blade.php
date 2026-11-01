<x-layouts.dashboard :title="$quote->exists ? 'Edit quote '.$quote->number : 'New quotation'" area="admin">
    <x-slot:subtitle>
        <a href="{{ $quote->exists ? route('admin.quotes.show', $quote) : route('admin.quotes.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← {{ $quote->exists ? 'Back to quote' : 'All quotes' }}</a>
        @if ($quote->enquiry_id && ! $quote->exists)
            <span class="text-slate-500">· Started from enquiry #{{ str_pad($quote->enquiry_id, 5, '0', STR_PAD_LEFT) }}</span>
        @endif
        <span class="text-slate-500">· A price offer for the customer. Stock and payments only start once it becomes an invoice.</span>
    </x-slot:subtitle>

    <x-admin.document-editor :document="$quote" type="quote" :customers="$customers" :inventory="$inventory" :services="$services" :items="$items"
                             :action="$quote->exists ? route('admin.quotes.update', $quote) : route('admin.quotes.store')"
                             :method="$quote->exists ? 'PUT' : 'POST'" />
</x-layouts.dashboard>
