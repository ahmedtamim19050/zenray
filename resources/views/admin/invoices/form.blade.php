<x-layouts.dashboard :title="$invoice->exists ? 'Edit invoice '.$invoice->number : 'New invoice'" area="admin">
    <x-slot:subtitle>
        <a href="{{ $invoice->exists ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← {{ $invoice->exists ? 'Back to invoice' : 'All invoices' }}</a>
        <span class="text-slate-500">· Saved as a draft. Stock is taken out of inventory when you issue it.</span>
    </x-slot:subtitle>

    <x-admin.document-editor :document="$invoice" type="invoice" :customers="$customers" :inventory="$inventory" :services="$services" :items="$items"
                             :action="$invoice->exists ? route('admin.invoices.update', $invoice) : route('admin.invoices.store')"
                             :method="$invoice->exists ? 'PUT' : 'POST'" />
</x-layouts.dashboard>
