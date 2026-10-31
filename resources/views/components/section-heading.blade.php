@props(['eyebrow' => null, 'title', 'id' => null, 'align' => 'left', 'dark' => false])

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p @class([
            'mb-3 inline-flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase',
            'text-signal-700' => ! $dark,
            'font-mono text-glow-300' => $dark,
        ])>
            @if ($dark)
                <span class="hud-blink size-1.5 bg-glow-300 shadow-[0_0_8px_rgb(46_199_240)]" aria-hidden="true"></span>
            @else
                <span class="h-px w-6 bg-current" aria-hidden="true"></span>
            @endif
            {{ $eyebrow }}
        </p>
    @endif
    <h2 @if ($id) id="{{ $id }}" @endif data-split @class([
        'text-[2rem] leading-[1.08] font-bold tracking-[-0.03em] text-balance sm:text-5xl',
        'text-navy-800' => ! $dark,
        'text-ink' => $dark,
    ])>{{ $title }}</h2>
    @isset($description)
        <p @class([
            'mt-4 text-base leading-relaxed text-pretty sm:text-lg',
            'text-slate-600' => ! $dark,
            'text-ink-2' => $dark,
        ])>{{ $description }}</p>
    @endisset
</div>
