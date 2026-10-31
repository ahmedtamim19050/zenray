@props(['label', 'value', 'icon' => 'layers', 'hint' => null, 'tone' => 'navy', 'href' => null])

@php
    $tones = [
        'navy' => 'bg-navy-800 text-white',
        'signal' => 'bg-signal-500 text-navy-950',
        'sky' => 'bg-sky-500 text-white',
        'slate' => 'bg-slate-200 text-navy-800',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif data-spotlight
    {{ $attributes->class('group relative block overflow-hidden rounded-2xl bg-white p-5 ring-1 ring-navy-100 transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover sm:p-6') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            {{-- Long values (e.g. money) use a smaller size so they never push the icon out --}}
            <p @class([
                'mt-2 font-bold tracking-tight break-words text-navy-900 tabular-nums',
                'text-3xl sm:text-4xl' => mb_strlen((string) $value) <= 7,
                'text-2xl sm:text-[1.75rem]' => mb_strlen((string) $value) > 7,
            ])>{{ $value }}</p>
        </div>
        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] }} transition-transform duration-300 group-hover:scale-110">
            <x-icon :name="$icon" class="size-5" />
        </span>
    </div>
    @if ($hint)
        <p class="mt-3 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</{{ $tag }}>
