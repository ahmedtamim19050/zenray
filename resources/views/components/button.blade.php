@props(['href', 'variant' => 'primary', 'size' => 'md', 'icon' => null])

@php
    $variants = [
        'primary' => 'bg-signal-500 text-navy-950 hover:bg-signal-400 shadow-[0_1px_0_rgb(255_255_255/0.4)_inset,0_6px_16px_-6px_rgb(242_140_40/0.6)]',
        'navy' => 'bg-navy-800 text-white hover:bg-navy-700 shadow-[0_6px_16px_-8px_rgb(11_45_77/0.6)]',
        'outline' => 'border border-navy-200 bg-white text-navy-800 hover:border-navy-800',
        'outline-light' => 'border border-white/35 text-white hover:border-white hover:bg-white/10',
        'ghost' => 'text-navy-800 hover:bg-navy-50',
        // Public X-ray theme (dark film backgrounds)
        'glow' => 'bg-glow-400/10 text-glow-50 ring-1 ring-inset ring-glow-400/45 hover:bg-glow-400/20 hover:ring-glow-300 shadow-[0_0_28px_-8px_rgb(46_199_240/0.6)]',
        'outline-dark' => 'border border-film-500 bg-film-900/40 text-ink hover:border-glow-400 hover:text-glow-100',
    ];

    $sizes = [
        'sm' => 'h-10 px-5 text-sm',
        'md' => 'h-12 px-6 text-[0.9375rem]',
        'lg' => 'h-14 px-7 text-base',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->class([
    'group inline-flex items-center justify-center gap-2 rounded-full font-semibold whitespace-nowrap transition duration-200 active:scale-[0.98]',
    $variants[$variant],
    $sizes[$size],
]) }}>
    {{ $slot }}
    @if ($icon)
        <x-icon :name="$icon" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
    @endif
</a>
