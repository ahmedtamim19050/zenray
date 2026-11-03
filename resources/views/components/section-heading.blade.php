@props(['eyebrow' => null, 'title', 'id' => null, 'align' => 'left', 'dark' => false])

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p @class([
            'mb-3 text-sm font-semibold',
            'text-signal-700' => ! $dark,
            'text-signal-text' => $dark,
        ])>{{ $eyebrow }}</p>
    @endif
    <h2 @if ($id) id="{{ $id }}" @endif data-reveal @class([
        'text-2xl leading-tight font-semibold tracking-tight text-balance sm:text-3xl',
        'text-navy-800' => ! $dark,
        'text-ink' => $dark,
    ])>{{ $title }}</h2>
    @isset($description)
        <p @class([
            'mt-4 text-base leading-relaxed text-pretty',
            'text-slate-600' => ! $dark,
            'text-ink-2' => $dark,
        ])>{{ $description }}</p>
    @endisset
</div>
