{{--
    Photo gallery with a lightbox (native <dialog>). Each tile is a real link to
    the full-size image, so it still works without JavaScript.
--}}
@props(['items'])

@php
    $spans = [
        'large' => 'sm:col-span-2 sm:row-span-2',
        'tall' => 'sm:row-span-2',
        'wide' => 'sm:col-span-2',
        'normal' => '',
    ];
@endphp

<div {{ $attributes }}>
    <ul data-stagger class="grid auto-rows-[14rem] grid-cols-1 gap-3 grid-flow-dense sm:auto-rows-[13rem] sm:grid-cols-2 sm:gap-4 lg:grid-cols-4" data-gallery>
        @foreach ($items as $item)
            <li class="{{ $spans[$item['size']] ?? '' }}">
                <a href="{{ \App\Support\Photo::url($item['photo']) }}"
                   class="group relative block size-full overflow-hidden rounded-2xl bg-film-700"
                   data-lightbox-item data-caption="{{ $item['caption'] }}">
                    <x-photo :name="$item['photo']" :alt="$item['caption']"
                             sizes="(min-width: 1024px) 50vw, (min-width: 640px) 50vw, 100vw"
                             class="xray-hover transition duration-[900ms] ease-out group-hover:scale-[1.04]" />
                    <span class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 bg-linear-to-t from-navy-950/80 to-transparent p-4 pt-12">
                        <span class="text-sm font-medium text-white">{{ $item['caption'] }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-film-800/90 text-ink opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true">
                            <x-icon name="expand" class="size-4" />
                        </span>
                    </span>
                    <span class="sr-only">(open larger image)</span>
                </a>
            </li>
        @endforeach
    </ul>

    <dialog class="m-auto w-[min(72rem,calc(100vw-2rem))] max-w-none overflow-visible bg-transparent p-0 backdrop:bg-navy-950/85 backdrop:backdrop-blur-sm" aria-label="Image viewer" data-lightbox>
        <figure class="relative overflow-hidden rounded-2xl bg-film-950">
            <img src="" alt="" class="max-h-[78vh] w-full object-contain" data-lightbox-image>
            <figcaption class="flex items-center justify-between gap-4 px-5 py-4 text-sm text-navy-100">
                <span data-lightbox-caption></span>
                <span class="shrink-0 font-mono text-navy-300" data-lightbox-count></span>
            </figcaption>
        </figure>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" class="flex size-11 items-center justify-center rounded-full bg-film-800 text-ink transition hover:bg-signal-500" data-lightbox-prev>
                <x-icon name="chevron-left" class="size-5" /><span class="sr-only">Previous image</span>
            </button>
            <button type="button" class="flex size-11 items-center justify-center rounded-full bg-film-800 text-ink transition hover:bg-signal-500" data-lightbox-next>
                <x-icon name="chevron-right" class="size-5" /><span class="sr-only">Next image</span>
            </button>
            <button type="button" class="flex size-11 items-center justify-center rounded-full bg-film-800 text-ink transition hover:bg-signal-500" data-lightbox-close autofocus>
                <x-icon name="close" class="size-5" /><span class="sr-only">Close image viewer</span>
            </button>
        </div>
    </dialog>
</div>
