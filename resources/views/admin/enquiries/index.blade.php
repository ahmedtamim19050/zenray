<x-layouts.dashboard title="Enquiries" area="admin">
    <x-slot:subtitle>Quote requests and messages sent from the website.</x-slot:subtitle>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter by status">
            <ul class="flex w-max gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                @foreach (['' => 'All'] + collect(\App\Models\Enquiry::STATUSES)->map(fn ($s) => $s[0])->all() as $key => $label)
                    @php($active = $status === $key || ($key === '' && ! array_key_exists($status, \App\Models\Enquiry::STATUSES)))
                    <li>
                        <a href="{{ route('admin.enquiries.index', array_filter(['status' => $key, 'q' => $search])) }}" @if ($active) aria-current="page" @endif
                           @class(['inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition', 'bg-navy-800 text-white' => $active, 'text-slate-600 hover:text-navy-900' => ! $active])>
                            {{ $label }}
                            <span @class(['rounded-full px-1.5 font-mono text-xs', 'bg-signal-500 text-navy-950' => $active, 'bg-slate-100 text-slate-600' => ! $active])>{{ $key === '' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="GET" action="{{ route('admin.enquiries.index') }}" class="flex gap-2" role="search">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <label for="enquiry-search" class="sr-only">Search enquiries</label>
            <input id="enquiry-search" type="search" name="q" value="{{ $search }}" placeholder="Search name, email, organization…"
                   class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none lg:w-72">
            <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
        </form>
    </div>

    <x-dash.card :padded="false">
        @if ($enquiries->isEmpty())
            <x-dash.empty icon="mail" title="No enquiries found" :text="$search ? 'Try a different search.' : 'Enquiries from the website will appear here.'" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">From</th>
                            <th scope="col" class="px-6 py-3">Interest</th>
                            <th scope="col" class="px-6 py-3">Received</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($enquiries as $enquiry)
                            <tr @class(['transition hover:bg-slate-50', 'bg-signal-50/40' => $enquiry->status === 'new'])>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-navy-900">{{ $enquiry->name }}</p>
                                    <p class="text-slate-500">{{ $enquiry->organization ?: ($enquiry->email ?: $enquiry->phone) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-navy-900">{{ $enquiry->interestLabel() }}</p>
                                    <p class="text-slate-500">{{ $enquiry->typeLabel() }}@if ($enquiry->user) · <span class="text-sky-700">Customer account</span>@endif</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600" title="{{ $enquiry->created_at->format('j M Y H:i') }}">{{ $enquiry->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4"><x-dash.status :enquiry="$enquiry" /></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="inline-flex items-center gap-1 font-semibold text-navy-800 hover:text-signal-700">Open<span class="sr-only"> enquiry from {{ $enquiry->name }}</span> <x-icon name="arrow-right" class="size-4" /></a>
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
