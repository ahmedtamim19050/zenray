{{--
    Photo with an X-ray layer on top. "base" chooses which version shows by
    default (xray = radiograph negative, photo = original); the other version
    is revealed by a sweeping beam (scan) and/or a pointer lens (lens).
    The wrapper needs a size, e.g. an aspect-* class or absolute inset-0.
    <x-xray name="hero-room" alt="..." base="xray" scan lens class="aspect-[4/3]" />
--}}
@props(['name', 'alt', 'sizes' => '100vw', 'base' => 'photo', 'scan' => false, 'lens' => true, 'priority' => false, 'loading' => null, 'imgClass' => ''])

@php($baseIsXray = $base === 'xray')

<div {{ $attributes->class('xray-frame') }} @if ($scan) data-scan @endif @if ($lens) data-xray-lens @endif>
    <x-photo :name="$name" :alt="$alt" :sizes="$sizes" :priority="$priority" :loading="$loading"
             @class([$imgClass, 'is-xray' => $baseIsXray]) />
    <x-photo :name="$name" alt="" :sizes="$sizes" :loading="$priority ? 'eager' : $loading" aria-hidden="true"
             @class([$imgClass, 'xray-reveal', 'is-xray' => ! $baseIsXray]) />
    @if ($scan) <span class="xray-beam" aria-hidden="true"></span> @endif
    @if ($lens) <span class="xray-lens-ring" aria-hidden="true"></span> @endif
    {{ $slot }}
</div>
