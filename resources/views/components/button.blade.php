@props(['href', 'variant' => 'primary', 'size' => 'md', 'icon' => null])

@php
    $variants = [
        'primary' => 'bg-signal-500 text-navy-950 hover:bg-signal-400',
        'navy' => 'bg-navy-800 text-white hover:bg-navy-700',
        'outline' => 'border border-navy-200 bg-white text-navy-800 hover:border-navy-800',
        'outline-light' => 'border border-white/40 text-white hover:border-white hover:bg-white/10',
        'ghost' => 'text-navy-800 hover:bg-navy-50',
        // Public site surfaces, which follow the light / dark theme
        'glow' => 'bg-glow-400 text-film-950 hover:bg-glow-500',
        'outline-dark' => 'border border-film-500 bg-transparent text-ink hover:border-ink hover:bg-film-700',
    ];

    $sizes = [
        'sm' => 'min-h-11 px-4 py-2 text-sm',
        'md' => 'min-h-11 px-5 py-2 text-sm',
        'lg' => 'min-h-12 px-6 py-2 text-base',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->class([
    'group inline-flex items-center justify-center gap-2 rounded-full text-center font-semibold transition-colors sm:whitespace-nowrap duration-200',
    $variants[$variant],
    $sizes[$size],
]) }}>
    {{ $slot }}
    @if ($icon)
        <x-icon :name="$icon" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
    @endif
</a>
