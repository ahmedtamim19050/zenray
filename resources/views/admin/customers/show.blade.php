<x-layouts.dashboard :title="$customer->name" area="admin">
    <x-slot:subtitle>Customer since {{ $customer->created_at->format('j F Y') }}</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.customers.index')" variant="outline" size="sm">← All customers</x-button>
        <x-button :href="route('admin.invoices.create', ['customer' => $customer->id])" variant="outline" size="sm">New invoice</x-button>
        <x-button :href="route('admin.quotes.create', ['customer' => $customer->id])" size="sm" class="btn-shine">New quote</x-button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-dash.card title="Details">
            <dl class="space-y-3 text-sm">
                @foreach (['Email' => $customer->email, 'Phone' => $customer->phone, 'Organization' => $customer->organization, 'Enquiries' => $enquiries->count()] as $label => $value)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="truncate text-right font-medium text-navy-900">{{ filled($value) ? $value : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
            <div class="mt-5 flex flex-wrap gap-2">
                <x-button :href="'mailto:'.$customer->email" variant="navy" size="sm"><x-icon name="mail" class="size-4" /> Email</x-button>
                @if ($customer->phone)
                    <x-button :href="'tel:'.preg_replace('/[^\d+]/', '', $customer->phone)" variant="outline" size="sm"><x-icon name="phone" class="size-4" /> Call</x-button>
                @endif
            </div>
        </x-dash.card>

        <x-dash.card title="Enquiries" class="xl:col-span-2" :padded="false">
            @forelse ($enquiries as $enquiry)
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-4 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-navy-900">{{ $enquiry->interestLabel() }}</span>
                        <span class="block truncate text-sm text-slate-500">{{ $enquiry->typeLabel() }} · {{ $enquiry->created_at->format('j M Y') }}</span>
                    </span>
                    <x-dash.status :enquiry="$enquiry" />
                </a>
            @empty
                <x-dash.empty icon="mail" title="No enquiries from this customer yet" />
            @endforelse
        </x-dash.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        @foreach (['Quotes' => [$quotes, 'admin.quotes.show'], 'Invoices' => [$invoices, 'admin.invoices.show']] as $heading => [$documents, $route])
            <x-dash.card :title="$heading" :padded="false">
                @forelse ($documents as $document)
                    <a href="{{ route($route, $document) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-3.5 transition last:border-0 hover:bg-slate-50 sm:px-6">
                        <span class="min-w-0 flex-1">
                            <span class="block font-mono text-sm font-semibold text-navy-900">{{ $document->number }}</span>
                            <span class="block text-sm text-slate-500">{{ $document->issue_date->format('j M Y') }} · {{ $document->money('total') }}</span>
                        </span>
                        <x-admin.badge :state="$document->state()" />
                    </a>
                @empty
                    <x-dash.empty icon="document" :title="'No '.strtolower($heading).' yet'" />
                @endforelse
            </x-dash.card>
        @endforeach
    </div>
</x-layouts.dashboard>
