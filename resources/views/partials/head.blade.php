{{-- Shared <head> contents. Expects $pageTitle, $description; $noindex for private pages. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="theme-color" content="{{ $themeColor ?? '#0B2D4D' }}">

@if ($noindex ?? false)
    <meta name="robots" content="noindex, nofollow">
@else
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('zenray.name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($image ?? null)
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
@endif

<link rel="icon" href="{{ asset('images/zenray-mark.svg') }}" type="image/svg+xml">
<link rel="preload" href="{{ asset('images/zenray-logo.svg') }}" as="image" type="image/svg+xml">

@fonts

{{-- Mark JS as available before first paint so animated content doesn't flash --}}
<script>
    document.documentElement.classList.add('js');
    // Failsafe: if app.js never runs, show everything rather than leave content hidden.
    setTimeout(() => window.zenrayReady || document.documentElement.classList.remove('js'), 3000);
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
