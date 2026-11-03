<x-layouts.dashboard title="Overview">
    <x-slot:subtitle>Welcome back, {{ Str::before($user->name, ' ') }}. Here is a summary of your requests and invoices.</x-slot:subtitle>

    {{-- Welcome banner --}}
    <section class="relative isolate mb-8 overflow-hidden rounded-3xl bg-navy-900 p-6 text-white sm:p-8" data-reveal>
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute -top-24 -right-24 size-80 rounded-full bg-signal-500/30 blur-3xl"></div>
            <div class="bg-grid absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)]"></div>
        </div>
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-xl">
                <p class="text-xs font-semibold tracking-[0.18em] text-signal-400 uppercase">Customer portal</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Need equipment or support?</h2>
                <p class="mt-2 text-navy-200">Send a request and our team will get back to you. You can follow its progress here.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button :href="route('dashboard.enquiries.create')" icon="arrow-right" >New request</x-button>
                <x-button :href="route('products.index')" variant="outline-light">Browse equipment</x-button>
            </div>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-stagger>
        <x-dash.stat label="All requests" :value="$stats['total']" icon="document" :href="route('dashboard.enquiries.index')" />
        <x-dash.stat label="Open" :value="$stats['open']" icon="clock" tone="signal" hint="New or being handled" />
        <x-dash.stat label="Quotes sent" :value="$stats['quoted']" icon="mail" tone="sky" />
        <x-dash.stat label="Unpaid invoices" :value="$stats['unpaid']" icon="folder" tone="slate" :href="route('dashboard.invoices.index')" :hint="$stats['overdue'] ? $stats['overdue'].' overdue' : null" />
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
        <x-dash.card title="Recent requests" :padded="false">
            <x-slot:actions>
                <a href="{{ route('dashboard.enquiries.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">View all →</a>
            </x-slot:actions>

            @forelse ($recent as $enquiry)
                <a href="{{ route('dashboard.enquiries.show', $enquiry) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-4 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-navy-50 text-navy-700"><x-icon :name="$enquiry->type === 'quote' ? 'document' : 'chat'" class="size-5" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-navy-900">{{ $enquiry->interestLabel() }}</span>
                        <span class="block text-sm text-slate-500">{{ $enquiry->typeLabel() }} · {{ $enquiry->created_at->format('j M Y') }}</span>
                    </span>
                    <x-dash.status :enquiry="$enquiry" />
                </a>
            @empty
                <x-dash.empty icon="mail" title="No requests yet" text="When you send a quote request or question, it will appear here so you can follow its progress.">
                    <x-button :href="route('dashboard.enquiries.create')" size="sm">Send your first request</x-button>
                </x-dash.empty>
            @endforelse
        </x-dash.card>

        <x-dash.card title="Recent invoices" :padded="false">
            <x-slot:actions>
                <a href="{{ route('dashboard.invoices.index') }}" class="text-sm font-semibold text-navy-800 hover:text-signal-700">View all →</a>
            </x-slot:actions>

            @forelse ($recentInvoices as $invoice)
                <a href="{{ route('dashboard.invoices.show', $invoice) }}" class="flex items-center gap-4 border-b border-navy-50 px-5 py-4 transition last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-navy-50 text-navy-700"><x-icon name="folder" class="size-5" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-mono font-semibold text-navy-900">{{ $invoice->number }}</span>
                        <span class="block text-sm text-slate-500">{{ $invoice->issue_date->format('j M Y') }} · {{ $invoice->money('total') }}</span>
                    </span>
                    <x-admin.badge :state="$invoice->state()" />
                </a>
            @empty
                <x-dash.empty icon="folder" title="No invoices yet" text="Invoices from our team will appear here once they are issued." />
            @endforelse
        </x-dash.card>
        </div>

        <div class="space-y-6">
            <x-dash.card title="Your details">
                <dl class="space-y-3 text-sm">
                    @foreach (['Name' => $user->name, 'Organization' => $user->organization, 'Email' => $user->email, 'Phone' => $user->phone] as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="truncate text-right font-medium text-navy-900">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
                <a href="{{ route('profile.edit') }}" class="mt-5 inline-flex text-sm font-semibold text-navy-800 hover:text-signal-700">Edit details →</a>
            </x-dash.card>

            <x-dash.card title="Talk to our team">
                @php($contact = config('zenray.contact'))
                <ul class="space-y-3 text-sm">
                    @if ($contact['phone']) <li class="flex items-center gap-3"><x-icon name="phone" class="size-4 text-signal-600" /><a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}" class="font-medium text-navy-900">{{ $contact['phone'] }}</a></li> @endif
                    @if ($contact['email']) <li class="flex items-center gap-3"><x-icon name="mail" class="size-4 text-signal-600" /><a href="mailto:{{ $contact['email'] }}" class="font-medium text-navy-900">{{ $contact['email'] }}</a></li> @endif
                    @if ($contact['hours']) <li class="flex items-center gap-3"><x-icon name="clock" class="size-4 text-signal-600" />{{ $contact['hours'] }}</li> @endif
                    @unless ($contact['phone'] || $contact['email'])
                        <li class="text-slate-500">Use <a href="{{ route('dashboard.enquiries.create') }}" class="font-semibold text-navy-800 underline">a new request</a> to send us a message.</li>
                    @endunless
                </ul>
            </x-dash.card>
        </div>
    </div>
</x-layouts.dashboard>
