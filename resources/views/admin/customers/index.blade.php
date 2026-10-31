<x-layouts.dashboard title="Customers" area="admin">
    <x-slot:subtitle>People who have created an account on the website.</x-slot:subtitle>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-6 flex max-w-md gap-2" role="search">
        <label for="customer-search" class="sr-only">Search customers</label>
        <input id="customer-search" type="search" name="q" value="{{ $search }}" placeholder="Search name, email, organization…"
               class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none">
        <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
    </form>

    <x-dash.card :padded="false">
        @if ($customers->isEmpty())
            <x-dash.empty icon="users" title="No customers found" :text="$search ? 'Try a different search.' : 'Customers who register will appear here.'" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Phone</th>
                            <th scope="col" class="px-6 py-3">Enquiries</th>
                            <th scope="col" class="px-6 py-3">Joined</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($customers as $customer)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-signal-100 font-bold text-signal-700" aria-hidden="true">{{ Str::upper(Str::substr($customer->name, 0, 1)) }}</span>
                                        <div>
                                            <p class="font-semibold text-navy-900">{{ $customer->name }}</p>
                                            <p class="text-slate-500">{{ $customer->email }}@if ($customer->organization) · {{ $customer->organization }}@endif</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $customer->phone ?: '—' }}</td>
                                <td class="px-6 py-4 font-semibold text-navy-900 tabular-nums">{{ $customer->enquiries_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $customer->created_at->format('j M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center gap-1 font-semibold text-navy-800 hover:text-signal-700">View<span class="sr-only"> {{ $customer->name }}</span> <x-icon name="arrow-right" class="size-4" /></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $customers->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
