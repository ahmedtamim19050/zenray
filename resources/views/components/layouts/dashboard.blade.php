{{--
    Dashboard shell for customers and admins: sidebar, top bar, flash message.
    <x-layouts.dashboard title="Enquiries" area="admin"> ... </x-layouts.dashboard>
    Optional slots: $actions (top-right page buttons), $subtitle.
--}}
@props(['title', 'area' => 'customer'])

@php
    $user = auth()->user();
    $isAdmin = $area === 'admin';
    $newCount = $isAdmin ? \App\Models\Enquiry::query()->where('status', 'new')->count() : 0;

    // Sections of [label, url, icon, active?, badge?]. Website content links come from App\Support\AdminContent.
    $link = fn (string $label, string $route, string $icon, string $pattern, $badge = null) => [$label, route($route), $icon, request()->routeIs(...explode('|', $pattern)), $badge];
    $resource = request()->route('resource');

    $nav = $isAdmin
        ? [
            null => [
                $link('Overview', 'admin.dashboard', 'layers', 'admin.dashboard'),
                $link('Enquiries', 'admin.enquiries.index', 'mail', 'admin.enquiries.*', $newCount),
                $link('Customers', 'admin.customers.index', 'users', 'admin.customers.*'),
            ],
            'Sales' => [
                $link('Quotations', 'admin.quotes.index', 'document', 'admin.quotes.*'),
                $link('Invoices', 'admin.invoices.index', 'folder', 'admin.invoices.*', \App\Models\Invoice::query()->overdue()->count() ?: null),
                $link('Inventory', 'admin.inventory.index', 'layers', 'admin.inventory.*', \App\Models\InventoryItem::query()->where('is_active', true)->lowStock()->count() ?: null),
                $link('Reports', 'admin.reports', 'monitor', 'admin.reports'),
            ],
            'Blog' => [
                $link('Posts', 'admin.posts.index', 'document', 'admin.posts.*'),
                ['Categories', route('admin.content.index', 'blog-categories'), 'folder', $resource === 'blog-categories', null],
            ],
            'Website content' => collect(\App\Support\AdminContent::resources())
                ->except('blog-categories')
                ->map(fn ($r, $key) => [$r['plural'], route('admin.content.index', $key), $r['icon'], $resource === $key, null])
                ->values()->all(),
            'Account' => [
                $link('Settings', 'admin.settings.edit', 'support', 'admin.settings.*'),
                $link('My profile', 'profile.edit', 'user', 'profile.*'),
            ],
        ]
        : [
            null => [
                $link('Overview', 'dashboard', 'layers', 'dashboard'),
                $link('My enquiries', 'dashboard.enquiries.index', 'mail', 'dashboard.enquiries.index|dashboard.enquiries.show'),
                $link('New request', 'dashboard.enquiries.create', 'chat', 'dashboard.enquiries.create'),
                $link('My invoices', 'dashboard.invoices.index', 'folder', 'dashboard.invoices.*', $user->invoices()->overdue()->count() ?: null),
                $link('Profile & password', 'profile.edit', 'user', 'profile.*'),
            ],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="bg-slate-50">
    <head>
        @include('partials.head', ['pageTitle' => "{$title} | ".config('zenray.name'), 'description' => config('zenray.description'), 'noindex' => true])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
        <a href="#main" class="sr-only z-[60] rounded-md bg-navy-800 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

        {{-- Sidebar --}}
        <aside id="dash-sidebar" class="dash-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-navy-950 text-navy-100 transition-transform duration-300 lg:translate-x-0" aria-label="Dashboard">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -top-24 -left-24 size-72 rounded-full bg-signal-500/15 blur-3xl"></div>
                <div class="absolute -right-24 bottom-0 size-72 rounded-full bg-sky-500/10 blur-3xl"></div>
            </div>

            <div class="relative flex h-20 items-center justify-between px-6">
                <a href="{{ route('home') }}" class="rounded-lg bg-white px-3 py-2" aria-label="{{ config('zenray.name') }} website">
                    <img src="{{ asset('images/zenray-logo.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" class="h-7 w-auto">
                </a>
                <button type="button" class="flex size-10 items-center justify-center rounded-lg text-navy-200 hover:bg-white/10 lg:hidden" data-dash-close>
                    <x-icon name="close" class="size-5" /><span class="sr-only">Close menu</span>
                </button>
            </div>

            <p class="relative px-6 pt-2 text-[0.6875rem] font-semibold tracking-[0.18em] text-navy-400 uppercase">
                {{ $isAdmin ? 'Admin console' : 'Customer portal' }}
            </p>

            <nav class="relative mt-3 flex-1 overflow-y-auto px-3" aria-label="{{ $isAdmin ? 'Admin' : 'Account' }}">
                @foreach ($nav as $section => $items)
                    @if ($section)
                        <p class="mt-6 mb-2 px-3 text-[0.6875rem] font-semibold tracking-[0.18em] text-navy-400 uppercase">{{ $section }}</p>
                    @endif
                    <ul class="space-y-1">
                        @foreach ($items as [$label, $url, $icon, $active, $badge])
                            <li>
                                <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                                   @class([
                                       'group flex items-center gap-3 rounded-xl px-3 py-2 text-[0.9375rem] font-medium transition',
                                       'bg-white text-navy-900 shadow-lg shadow-black/20' => $active,
                                       'text-navy-200 hover:bg-white/5 hover:text-white' => ! $active,
                                   ])>
                                    <span @class([
                                        'flex size-8 items-center justify-center rounded-lg transition-colors',
                                        'bg-signal-500 text-navy-950' => $active,
                                        'bg-white/5 text-navy-300 group-hover:text-signal-400' => ! $active,
                                    ])>
                                        <x-icon :name="$icon" class="size-4" />
                                    </span>
                                    {{ $label }}
                                    @if ($badge)
                                        <span class="ml-auto rounded-full bg-signal-500 px-2 py-0.5 text-xs font-bold text-navy-950">{{ $badge }}<span class="sr-only"> need attention</span></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach

                <div class="mt-8 border-t border-white/10 pt-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[0.9375rem] font-medium text-navy-200 hover:bg-white/5 hover:text-white">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-white/5"><x-icon name="arrow-right" class="size-4 rotate-180" /></span>
                        Back to website
                    </a>
                </div>
            </nav>

            <div class="relative m-3 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-signal-400 to-signal-600 font-bold text-navy-950" aria-hidden="true">
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                        <p class="truncate text-xs text-navy-300">{{ $user->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="dash-overlay fixed inset-0 z-40 hidden bg-navy-950/60 backdrop-blur-sm lg:hidden" data-dash-close aria-hidden="true"></div>

        {{-- Main --}}
        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-navy-100 bg-white/85 backdrop-blur-xl">
                <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-10">
                    <button type="button" class="-ml-2 flex size-10 items-center justify-center rounded-lg text-navy-800 hover:bg-navy-50 lg:hidden" data-dash-open aria-controls="dash-sidebar" aria-expanded="false">
                        <x-icon name="menu" class="size-6" /><span class="sr-only">Open menu</span>
                    </button>
                    <p class="text-sm text-slate-500">
                        <span class="hidden sm:inline">{{ $isAdmin ? 'Admin' : 'My account' }} /</span>
                        <span class="font-semibold text-navy-900">{{ $title }}</span>
                    </p>
                    <div class="ml-auto flex items-center gap-2">
                        @if ($isAdmin)
                            <a href="{{ route('admin.enquiries.index', ['status' => 'new']) }}" class="relative flex size-10 items-center justify-center rounded-full text-navy-700 hover:bg-navy-50">
                                <x-icon name="mail" class="size-5" />
                                <span class="sr-only">{{ $newCount }} new enquiries</span>
                                @if ($newCount) <span class="absolute top-1.5 right-1.5 size-2.5 rounded-full bg-signal-500 ring-2 ring-white" aria-hidden="true"></span> @endif
                            </a>
                        @else
                            <x-button :href="route('dashboard.enquiries.create')" size="sm" class="hidden sm:inline-flex">New request</x-button>
                        @endif
                    </div>
                </div>
            </header>

            <main id="main" class="px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-navy-900 sm:text-3xl">{{ $title }}</h1>
                        @isset($subtitle) <p class="mt-1 text-slate-500">{{ $subtitle }}</p> @endisset
                    </div>
                    @isset($actions) <div class="flex flex-wrap gap-2">{{ $actions }}</div> @endisset
                </div>

                @if (session('status'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
                        <x-icon name="check" class="mt-0.5 size-4 shrink-0" />
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
