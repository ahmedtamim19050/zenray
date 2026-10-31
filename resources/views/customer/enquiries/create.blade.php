<x-layouts.dashboard title="New request">
    <x-slot:subtitle>Ask for a quotation or send our team a question. Your details are filled in from your profile.</x-slot:subtitle>

    <div class="grid gap-6 xl:grid-cols-3">
        <div id="enquiry" class="xl:col-span-2">
            @if (session('enquiry_status') === 'sent')
                <x-dash.card>
                    <x-dash.empty icon="check" title="Thank you, your request has been sent" text="You can follow its progress from My enquiries.">
                        <div class="flex flex-wrap justify-center gap-3">
                            <x-button :href="route('dashboard.enquiries.index')" variant="navy" size="sm">View my enquiries</x-button>
                            <x-button :href="route('dashboard.enquiries.create')" variant="outline" size="sm">Send another</x-button>
                        </div>
                    </x-dash.empty>
                </x-dash.card>
            @else
                <x-enquiry-form type="quote" />
            @endif
        </div>

        <x-dash.card title="Tips for a quicker reply">
            <ul class="space-y-3 text-sm text-slate-600">
                @foreach (['Mention the type of facility and room', 'Describe the equipment or service you need, in your own words', 'Add any timing or budget considerations', 'Not sure about technical details? That is fine.'] as $tip)
                    <li class="flex gap-3"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-signal-600" />{{ $tip }}</li>
                @endforeach
            </ul>
        </x-dash.card>
    </div>
</x-layouts.dashboard>
