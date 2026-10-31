<x-layouts.dashboard title="My enquiries">
    <x-slot:subtitle>Every quote request and message you have sent while signed in.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('dashboard.enquiries.create')" size="sm" icon="arrow-right">New request</x-button>
    </x-slot:actions>

    <x-dash.card :padded="false">
        @if ($enquiries->isEmpty())
            <x-dash.empty icon="mail" title="No enquiries yet" text="Requests you send while signed in will be listed here.">
                <x-button :href="route('dashboard.enquiries.create')" size="sm">Send a request</x-button>
            </x-dash.empty>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Request</th>
                            <th scope="col" class="px-6 py-3">Type</th>
                            <th scope="col" class="px-6 py-3">Sent</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($enquiries as $enquiry)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-navy-900">{{ $enquiry->interestLabel() }}</p>
                                    <p class="max-w-xs truncate text-slate-500">{{ $enquiry->message }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $enquiry->typeLabel() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $enquiry->created_at->format('j M Y') }}</td>
                                <td class="px-6 py-4"><x-dash.status :enquiry="$enquiry" /></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('dashboard.enquiries.show', $enquiry) }}" class="font-semibold text-navy-800 hover:text-signal-700">View<span class="sr-only"> request from {{ $enquiry->created_at->format('j M Y') }}</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($enquiries->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $enquiries->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
