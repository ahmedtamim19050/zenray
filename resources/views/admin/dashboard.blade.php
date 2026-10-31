<x-layouts.dashboard title="Overview" area="admin">
    <x-slot:subtitle>{{ now()->format('l j F Y') }} · All figures come from enquiries and accounts on this website.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.index', ['status' => 'new'])" variant="navy" size="sm" icon="arrow-right">Review new enquiries</x-button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-stagger>
        <x-dash.stat label="New enquiries" :value="$stats['new']" icon="mail" tone="signal" hint="Waiting for a first response" :href="route('admin.enquiries.index', ['status' => 'new'])" />
        <x-dash.stat label="Open enquiries" :value="$stats['open']" icon="clock" hint="New, in progress or quoted" :href="route('admin.enquiries.index')" />
        <x-dash.stat label="Last 7 days" :value="$stats['last7']" icon="layers" tone="sky" hint="Enquiries received" />
        <x-dash.stat label="Customers" :value="$stats['customers']" icon="users" tone="slate" hint="Registered accounts" :href="route('admin.customers.index')" />
    </div>

    <h2 class="mt-8 mb-3 text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Sales</h2>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-stagger>
        <x-dash.stat label="Received this month" :value="\App\Support\Money::format($sales['received_month'])" icon="check" tone="sky" :hint="now()->format('F Y')" :href="route('admin.reports')" />
        <x-dash.stat label="Outstanding invoices" :value="\App\Support\Money::format($sales['outstanding'])" icon="folder" :tone="$sales['overdue_count'] ? 'signal' : 'navy'"
                     :hint="$sales['overdue_count'] ? $sales['overdue_count'].' overdue' : 'Nothing overdue'" :href="route('admin.invoices.index', ['status' => $sales['overdue_count'] ? 'overdue' : 'unpaid'])" />
        <x-dash.stat label="Open quotes" :value="$sales['open_quotes']" icon="document" tone="slate" :hint="\App\Support\Money::format($sales['open_quotes_value']).' sent or accepted'" :href="route('admin.quotes.index')" />
        <x-dash.stat label="Low stock items" :value="$sales['low_stock']" icon="layers" :tone="$sales['low_stock'] ? 'signal' : 'slate'" hint="At or below reorder level" :href="route('admin.inventory.index', ['stock' => 'low'])" />
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <x-dash.card title="Recent enquiries" class="xl:col-span-2" :padded="false">
            <x-slot:actions>
                <a href="{{ route('admin.enquiries.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">View all →</a>
            </x-slot:actions>
            @forelse ($recent as $enquiry)
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-4 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sm font-bold text-white" aria-hidden="true">{{ Str::upper(Str::substr($enquiry->name, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-navy-900">{{ $enquiry->name }}@if ($enquiry->organization)<span class="font-normal text-slate-500"> · {{ $enquiry->organization }}</span>@endif</span>
                        <span class="block truncate text-sm text-slate-500">{{ $enquiry->typeLabel() }} · {{ $enquiry->interestLabel() }} · {{ $enquiry->created_at->diffForHumans() }}</span>
                    </span>
                    <x-dash.status :enquiry="$enquiry" />
                </a>
            @empty
                <x-dash.empty icon="mail" title="No enquiries yet" text="Quote requests and messages from the website will appear here." />
            @endforelse
        </x-dash.card>

        <div class="space-y-6">
            {{-- Single-series magnitude: sorted horizontal bars, one hue, direct labels --}}
            <x-dash.card title="Enquiries by status" description="Count of all enquiries">
                @php($statusMax = max(1, $byStatus->max() ?? 1))
                <ul class="space-y-3.5">
                    @foreach (\App\Models\Enquiry::STATUSES as $key => [$label])
                        @php($total = $byStatus[$key] ?? 0)
                        <li>
                            <a href="{{ route('admin.enquiries.index', ['status' => $key]) }}" class="group block" title="{{ $label }}: {{ $total }} {{ Str::plural('enquiry', $total) }}">
                                <span class="flex justify-between text-sm"><span class="text-slate-600 group-hover:text-navy-900">{{ $label }}</span><span class="font-semibold text-navy-900 tabular-nums">{{ $total }}</span></span>
                                <span class="mt-1.5 block h-2 rounded-full bg-slate-100">
                                    <span class="block h-2 rounded-full bg-navy-700 transition-colors group-hover:bg-navy-900" style="width: {{ $total ? max(2, round($total / $statusMax * 100)) : 0 }}%"></span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-dash.card>

            <x-dash.card title="What people ask about" description="Enquiries by area of interest">
                @if ($byInterest->isEmpty())
                    <p class="text-sm text-slate-500">No data yet.</p>
                @else
                    @php($interestMax = max(1, $byInterest->max()))
                    <ul class="space-y-3.5">
                        @foreach ($byInterest->take(6) as $label => $total)
                            <li title="{{ $label }}: {{ $total }} {{ Str::plural('enquiry', $total) }}">
                                <span class="flex justify-between gap-3 text-sm"><span class="truncate text-slate-600">{{ $label }}</span><span class="font-semibold text-navy-900 tabular-nums">{{ $total }}</span></span>
                                <span class="mt-1.5 block h-2 rounded-full bg-slate-100">
                                    <span class="block h-2 rounded-full bg-signal-500" style="width: {{ max(2, round($total / $interestMax * 100)) }}%"></span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-dash.card>
        </div>
    </div>

    <x-dash.card title="Recent invoices" class="mt-6" :padded="false">
        <x-slot:actions>
            <a href="{{ route('admin.invoices.create') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">New invoice</a>
            <span class="text-navy-200" aria-hidden="true">·</span>
            <a href="{{ route('admin.invoices.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">All invoices →</a>
        </x-slot:actions>
        @forelse ($recentInvoices as $invoice)
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-3.5 transition last:border-0 hover:bg-slate-50 sm:px-6">
                <span class="w-36 shrink-0 font-mono text-sm font-semibold text-navy-900">{{ $invoice->number }}</span>
                <span class="min-w-0 flex-1 truncate text-sm text-slate-600">{{ $invoice->customer_name }}{{ $invoice->customer_organization ? ' · '.$invoice->customer_organization : '' }}</span>
                <span class="hidden text-sm font-semibold text-navy-900 tabular-nums sm:block">{{ $invoice->money('total') }}</span>
                <x-admin.badge :state="$invoice->state()" />
            </a>
        @empty
            <x-dash.empty icon="folder" title="No invoices yet" text="Create quotes and invoices from the Sales section.">
                <x-button :href="route('admin.quotes.create')" size="sm">Create a quote</x-button>
            </x-dash.empty>
        @endforelse
    </x-dash.card>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-dash.card title="Newest customers" class="xl:col-span-2" :padded="false">
            <x-slot:actions>
                <a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">All customers →</a>
            </x-slot:actions>
            @forelse ($newCustomers as $customer)
                <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-3.5 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-signal-100 text-sm font-bold text-signal-700" aria-hidden="true">{{ Str::upper(Str::substr($customer->name, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-navy-900">{{ $customer->name }}</span>
                        <span class="block truncate text-sm text-slate-500">{{ $customer->organization ?: $customer->email }}</span>
                    </span>
                    <span class="text-sm text-slate-500">{{ $customer->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <x-dash.empty icon="users" title="No customer accounts yet" text="Customers who register on the website will appear here." />
            @endforelse
        </x-dash.card>

        <x-dash.card title="Quick actions">
            <ul class="grid gap-2">
                @foreach ([['New quotation', route('admin.quotes.create'), 'document'], ['New invoice', route('admin.invoices.create'), 'folder'], ['Receive stock', route('admin.inventory.index'), 'layers'], ['Write a blog post', route('admin.posts.create'), 'chat'], ['Add a product', route('admin.content.create', 'products'), 'xray'], ['Edit homepage slides', route('admin.content.index', 'hero-slides'), 'monitor'], ['Edit contact information', route('admin.settings.edit').'#contact', 'phone'], ['Social media links', route('admin.settings.edit').'#social', 'users'], ['View the website', route('home'), 'arrow-right']] as [$label, $href, $icon])
                    <li>
                        <a href="{{ $href }}" class="group flex items-center gap-3 rounded-xl p-3 ring-1 ring-navy-100 transition hover:bg-slate-50 hover:ring-navy-200">
                            <span class="flex size-9 items-center justify-center rounded-lg bg-navy-50 text-navy-700 transition-colors group-hover:bg-signal-500 group-hover:text-navy-950"><x-icon :name="$icon" class="size-4" /></span>
                            <span class="text-sm font-semibold text-navy-900">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-dash.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-dash.card title="Latest blog posts" class="xl:col-span-2" :padded="false">
            <x-slot:actions>
                <a href="{{ route('admin.posts.create') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">New post</a>
                <span class="text-navy-200" aria-hidden="true">·</span>
                <a href="{{ route('admin.posts.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">All posts →</a>
            </x-slot:actions>
            @forelse ($posts as $post)
                @php([$stateLabel, $stateClasses] = $post->state())
                <a href="{{ route('admin.posts.edit', $post) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-3.5 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="relative size-10 shrink-0 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-navy-100">
                        @if ($post->photo) <x-photo :name="$post->photo" alt="" sizes="40px" /> @else <x-icon name="document" class="absolute inset-0 m-auto size-4 text-slate-300" /> @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-navy-900">{{ $post->title }}</span>
                        <span class="block truncate text-sm text-slate-500">{{ $post->category?->name ?? 'No category' }} · {{ $post->published_at?->format('j M Y') ?? 'Not scheduled' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $stateClasses }}">
                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $stateLabel }}
                    </span>
                </a>
            @empty
                <x-dash.empty icon="document" title="No blog posts yet" text="Articles help customers understand equipment and find your website.">
                    <x-button :href="route('admin.posts.create')" size="sm">Write the first post</x-button>
                </x-dash.empty>
            @endforelse
        </x-dash.card>

        <x-dash.card title="Website content" description="Items shown on the website">
            <ul class="divide-y divide-navy-50">
                @foreach ($content as $key => [$label, $icon, $visible, $total])
                    <li>
                        <a href="{{ route('admin.content.index', $key) }}" class="group -mx-2 flex items-center gap-3 rounded-lg px-2 py-2.5 transition hover:bg-slate-50">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-navy-50 text-navy-700 transition-colors group-hover:bg-signal-500 group-hover:text-navy-950"><x-icon :name="$icon" class="size-4" /></span>
                            <span class="flex-1 text-sm font-medium text-navy-900">{{ $label }}</span>
                            <span class="text-sm text-slate-500 tabular-nums">
                                <span class="font-semibold text-navy-900">{{ $visible }}</span>@if ($visible !== $total)<span> / {{ $total }}</span><span class="sr-only"> visible</span>@endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-dash.card>
    </div>
</x-layouts.dashboard>
