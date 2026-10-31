<x-layouts.dashboard :title="$enquiry->typeLabel()">
    <x-slot:subtitle>Sent {{ $enquiry->created_at->format('j F Y, H:i') }}</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('dashboard.enquiries.index')" variant="outline" size="sm">← All enquiries</x-button>
    </x-slot:actions>

    @php
        $steps = ['new' => 'Received', 'in_progress' => 'In progress', 'quoted' => 'Quote sent', 'closed' => 'Closed'];
        $reached = array_search($enquiry->status, array_keys($steps), true);
    @endphp

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-dash.card title="Progress">
                <ol class="grid grid-cols-4 gap-2" aria-label="Request progress">
                    @foreach ($steps as $key => $label)
                        @php($done = $loop->index <= $reached)
                        <li class="text-center">
                            <span @class(['block h-1.5 rounded-full', 'bg-signal-500' => $done, 'bg-navy-100' => ! $done])></span>
                            <span @class(['mt-2 block text-xs font-semibold sm:text-sm', 'text-navy-900' => $done, 'text-slate-400' => ! $done])>
                                {{ $label }}@if ($loop->index === $reached)<span class="sr-only"> (current)</span>@endif
                            </span>
                        </li>
                    @endforeach
                </ol>
            </x-dash.card>

            <x-dash.card title="Your message">
                <p class="text-sm font-semibold text-signal-700">{{ $enquiry->interestLabel() }}</p>
                <p class="mt-3 leading-relaxed whitespace-pre-line text-slate-700">{{ $enquiry->message }}</p>
            </x-dash.card>
        </div>

        <x-dash.card title="Details">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-dash.status :enquiry="$enquiry" /></dd></div>
                @foreach (['Reference' => '#'.str_pad($enquiry->id, 5, '0', STR_PAD_LEFT), 'Name' => $enquiry->name, 'Organization' => $enquiry->organization, 'Email' => $enquiry->email, 'Phone' => $enquiry->phone, 'Last updated' => $enquiry->updated_at->diffForHumans()] as $label => $value)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="truncate text-right font-medium text-navy-900">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-dash.card>
    </div>
</x-layouts.dashboard>
