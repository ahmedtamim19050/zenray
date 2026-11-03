@php
    $navigation = config('zenray.navigation');
    $categories = config('zenray.product_categories');
    $services = config('zenray.services');
    $contact = config('zenray.contact');
    $href = fn (array $item) => route($item['route']).(isset($item['fragment']) ? '#'.$item['fragment'] : '');
@endphp

{{-- Utility bar --}}
<div class="border-b border-film-600 bg-film-950 text-xs text-ink-3">
    <div class="mx-auto flex h-10 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <p class="hidden items-center gap-2 truncate sm:flex">Medical imaging equipment, installation &amp; technical support</p>
        <ul class="flex items-center gap-6">
            @if ($contact['phone'])
                <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}" class="inline-flex items-center gap-1.5 hover:text-ink"><x-icon name="phone" class="size-3.5 text-signal-text" />{{ $contact['phone'] }}</a></li>
            @endif
            @if ($contact['email'])
                <li class="hidden md:block"><a href="mailto:{{ $contact['email'] }}" class="inline-flex items-center gap-1.5 hover:text-ink"><x-icon name="mail" class="size-3.5 text-signal-text" />{{ $contact['email'] }}</a></li>
            @endif
            @unless ($contact['phone'] || $contact['email'])
                <li><a href="{{ route('contact') }}" class="inline-flex items-center gap-1.5 hover:text-ink"><x-icon name="chat" class="size-3.5 text-signal-text" />Talk to our team</a></li>
            @endunless
            <li class="hidden xl:block">
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center gap-1.5 font-medium text-ink-2 hover:text-ink">
                        <x-icon name="user" class="size-4" />
                        {{ auth()->user()->isAdmin() ? 'Admin' : 'My account' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 font-medium text-ink-2 hover:text-ink">
                        <x-icon name="user" class="size-4" />
                        Login
                    </a>
                @endauth
            </li>
            <li class="xl:hidden"><a href="{{ route('quote') }}" class="font-semibold text-signal-text hover:text-signal-text">Request a Quote</a></li>
        </ul>
    </div>
</div>

<header class="site-header sticky top-0 z-50 border-b border-film-600 bg-film-950" data-site-header>
    <div class="header-bar relative mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0 rounded-lg" aria-label="{{ config('zenray.name') }} home">
            <x-site.logo class="h-9 w-auto" />
        </a>

        <nav class="hidden xl:block" aria-label="Main">
            <ul class="flex items-center gap-0.5">
                @foreach ($navigation as $item)
                    @php($active = request()->routeIs($item['route'], Str::beforeLast($item['route'], '.').'.*') && empty($item['fragment']))
                    @php($menu = $item['menu'] ?? null)
                    <li @class(['relative' => $menu === 'services']) @if ($menu) data-menu @endif>
                        @if ($menu)
                            <button type="button" data-menu-button aria-expanded="false" aria-controls="menu-{{ $menu }}"
                                    class="group inline-flex items-center gap-1 rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap text-ink-2 transition-colors hover:bg-film-700 hover:text-ink aria-expanded:bg-film-700 aria-expanded:text-ink">
                                {{ $item['label'] }}
                                <x-icon name="chevron-down" class="size-4 transition-transform duration-200 group-aria-expanded:rotate-180" />
                            </button>
                        @else
                            <a href="{{ $href($item) }}" @if ($active) aria-current="page" @endif
                               @class([
                                   'inline-flex rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                                   'text-ink after:absolute' => $active,
                                   'text-ink-2 hover:bg-film-700 hover:text-ink' => ! $active,
                               ])>
                                <span @class(['relative', 'after:absolute after:inset-x-0 after:-bottom-1 after:h-0.5 after:rounded-full after:bg-signal-500' => $active])>{{ $item['label'] }}</span>
                            </a>
                        @endif

                        @if ($menu === 'equipment')
                            <div id="menu-equipment" hidden data-menu-panel class="absolute inset-x-0 top-full px-4 pt-3 sm:px-6 lg:px-8">
                                <div class="mx-auto grid max-w-5xl gap-2 rounded-2xl bg-film-800 p-3 shadow-card-hover ring-1 ring-film-600 lg:grid-cols-3">
                                    <ul class="grid gap-1 sm:grid-cols-2 lg:col-span-2">
                                        @foreach ($categories as $category)
                                            <li>
                                                <a href="{{ route('products.index') }}#{{ $category['slug'] }}"
                                                   class="group/item flex gap-4 rounded-2xl p-3 transition-colors hover:bg-film-700">
                                                    <span class="size-16 shrink-0 overflow-hidden rounded-xl bg-film-700">
                                                        <x-photo :name="$category['photo']" alt="" sizes="64px" loading="eager" />
                                                    </span>
                                                    <span>
                                                        <span class="flex items-center gap-1 font-semibold text-ink">
                                                            {{ $category['name'] }}
                                                            <x-icon name="arrow-right" class="size-3.5 -translate-x-1 text-signal-text opacity-0 transition group-hover/item:translate-x-0 group-hover/item:opacity-100" />
                                                        </span>
                                                        <span class="mt-0.5 line-clamp-2 block text-sm leading-snug text-ink-3">{{ $category['summary'] }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl bg-film-900 p-6 text-ink">
                                        <div class="relative">
                                            <p class="text-xs font-semibold text-signal-text">Need guidance?</p>
                                            <p class="mt-2 text-lg leading-snug font-bold">Not sure which equipment suits your facility?</p>
                                            <p class="mt-2 text-sm text-ink-2">Tell us about your needs and our team will suggest suitable options.</p>
                                        </div>
                                        <div class="relative mt-6 grid gap-2">
                                            <x-button :href="route('quote')" size="sm">Request a Quote</x-button>
                                            <a href="{{ route('products.index') }}" class="text-center text-sm font-semibold text-ink underline-offset-4 hover:underline">View all equipment →</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($menu === 'services')
                            <div id="menu-services" hidden data-menu-panel class="absolute top-full left-1/2 w-[26rem] -translate-x-1/2 pt-3">
                                <ul class="rounded-2xl bg-film-800 p-2 shadow-card-hover ring-1 ring-film-600">
                                    @foreach ($services as $service)
                                        <li>
                                            <a href="{{ route('services.index') }}#{{ $service['slug'] }}" class="group/item flex gap-3 rounded-2xl p-3 transition-colors hover:bg-film-700">
                                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-film-700 text-glow-300 transition-colors group-hover/item:bg-signal-500 group-hover/item:text-navy-950">
                                                    <x-icon :name="$service['icon']" class="size-5" />
                                                </span>
                                                <span>
                                                    <span class="block font-semibold text-ink">{{ $service['name'] }}</span>
                                                    <span class="mt-0.5 line-clamp-1 block text-sm text-ink-3">{{ $service['summary'] }}</span>
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-1.5">
            <x-site.theme-toggle />

            <div class="hidden items-center gap-1.5 xl:flex">
                <x-button :href="route('quote')" size="sm">Request a Quote</x-button>
            </div>

            <button type="button" data-nav-toggle aria-controls="mobile-menu" aria-expanded="false"
                    class="group inline-flex size-11 items-center justify-center rounded-xl text-ink transition-colors hover:bg-film-700 xl:hidden">
                <span class="sr-only" data-nav-label>Open menu</span>
                <x-icon name="menu" class="size-6 group-aria-expanded:hidden" />
                <x-icon name="close" class="hidden size-6 group-aria-expanded:block" />
            </button>
        </div>
    </div>

    <div id="mobile-menu" hidden class="absolute inset-x-0 top-full h-[calc(100dvh-4rem)] overflow-y-auto border-t border-film-600 bg-film-800 xl:hidden">
        <nav class="px-5 pt-2 pb-14" aria-label="Mobile">
            <ul class="divide-y divide-film-600">
                @foreach ($navigation as $item)
                    @php($active = request()->routeIs($item['route'], Str::beforeLast($item['route'], '.').'.*') && empty($item['fragment']))
                    <li class="py-1">
                        <a href="{{ $href($item) }}" @if ($active) aria-current="page" @endif
                           @class(['flex items-center justify-between py-3 text-lg font-semibold', 'text-ink' => ! $active, 'text-signal-text' => $active])>
                            {{ $item['label'] }}
                            <x-icon name="arrow-right" class="size-4 text-ink-3" />
                        </a>
                        @if (($item['menu'] ?? null) === 'equipment')
                            <ul class="mb-3 grid grid-cols-2 gap-2">
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('products.index') }}#{{ $category['slug'] }}"
                                           class="block rounded-xl bg-film-900 px-3 py-2.5 text-sm font-medium text-ink">{{ $category['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 grid gap-3">
                <x-button :href="route('quote')" class="w-full">Request a Quote</x-button>
                @auth
                    <x-button :href="auth()->user()->homeRoute()" variant="outline-dark" class="w-full">
                        <x-icon name="user" class="size-4" />
                        {{ auth()->user()->isAdmin() ? 'Admin dashboard' : 'My account' }}
                    </x-button>
                @else
                    <x-button :href="route('login')" variant="outline-dark" class="w-full">
                        <x-icon name="user" class="size-4" />
                        Login
                    </x-button>
                @endauth
            </div>
        </nav>
    </div>
</header>
