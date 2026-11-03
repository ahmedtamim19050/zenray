@props([
    'title' => null,
    'description' => config('zenray.description'),
    'noindex' => false,
    'image' => null,
])

@php
    $brand = config('zenray.name');
    $pageTitle = $title ? "{$title} | {$brand}" : "{$brand} | ".config('zenray.tagline');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
    <head>
        {{-- Light is the default. Apply a saved dark choice before first paint so the page never flashes. --}}
        <script>
            try {
                if (localStorage.getItem('zenray-theme') === 'dark') document.documentElement.dataset.theme = 'dark';
            } catch (error) {}
        </script>
        @include('partials.head', ['pageTitle' => $pageTitle, 'description' => $description, 'noindex' => $noindex, 'image' => $image, 'themeColor' => '#FFFFFF'])
    </head>
    <body class="xray flex min-h-screen flex-col bg-film-950 font-sans text-ink-2 antialiased">
        <a href="#main" class="sr-only z-[80] rounded-md bg-glow-400 px-4 py-2 font-semibold text-film-950 focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
            Skip to content
        </a>

        <x-site.header />

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        <x-site.footer />
    </body>
</html>
