{{--
    Responsive photo: a key from config('zenray.photos') (responsive WebP set)
    or the path of an image uploaded from the admin (see App\Support\Photo).
    Usage: <x-photo name="hero-room" alt="..." sizes="(min-width: 1024px) 50vw, 100vw" priority />
--}}
@props(['name', 'alt', 'sizes' => '100vw', 'priority' => false, 'loading' => null])

@php
    $photo = config("zenray.photos.{$name}");
    $loadingAttrs = $priority ? ['fetchpriority' => 'high'] : ['loading' => $loading ?? 'lazy'];
@endphp

@if (is_array($photo))
    @php
        $srcset = collect($photo['widths'])
            ->map(fn ($w) => asset("images/photos/{$name}-{$w}.webp")." {$w}w")
            ->implode(', ');
        $fallback = $photo['widths'][(int) floor((count($photo['widths']) - 1) / 2)];
    @endphp
    <img src="{{ asset("images/photos/{$name}-{$fallback}.webp") }}"
         srcset="{{ $srcset }}"
         sizes="{{ $sizes }}"
         alt="{{ $alt }}"
         width="{{ $photo['width'] }}"
         height="{{ $photo['height'] }}"
         decoding="async"
         {{ $attributes->merge($loadingAttrs)->merge(['class' => 'size-full object-cover']) }}>
@elseif (filled($name))
    <img src="{{ \App\Support\Photo::url($name) }}" alt="{{ $alt }}" decoding="async"
         {{ $attributes->merge($loadingAttrs)->merge(['class' => 'size-full object-cover']) }}>
@endif
