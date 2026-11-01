{{--
    Film viewer gallery with a lightbox (native <dialog>). Panels show as X-ray
    negatives and develop into colour when active: hovering or focusing opens one
    on large screens, swiping centres one on small screens. Each panel is a real
    link to the full-size image, so it still works without JavaScript.
    Behaviour lives in app.js ([data-gallery]), styles in app.css (.gallery-*).
--}}
@props(['items'])

@php
    // The first "large" photo starts open.
    $start = (int) array_search('large', array_column($items, 'size'), true);
    $total = count($items);
@endphp

<div {{ $attributes }}>
    <div data-reveal data-gallery data-gallery-start="{{ $start }}">
        <ul class="gallery-track relative -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto py-1 sm:-mx-6 lg:mx-0 lg:snap-none lg:gap-2 lg:py-0" data-gallery-track>
            @foreach ($items as $item)
                <li @class(['gallery-panel relative shrink-0 snap-center overflow-hidden rounded-2xl bg-film-700 ring-1 ring-film-600', 'is-active' => $loop->index === $start]) data-gallery-panel>
                    <a href="{{ \App\Support\Photo::url($item['photo']) }}"
                       class="group block size-full -outline-offset-4"
                       data-lightbox-item data-caption="{{ $item['caption'] }}">
                        <x-photo :name="$item['photo']" :alt="$item['caption']"
                                 sizes="(min-width: 1024px) 60vw, (min-width: 640px) 58vw, 82vw"
                                 class="gallery-photo absolute inset-0" />

                        <span class="gallery-scan pointer-events-none absolute inset-y-0 left-0 w-2/5" aria-hidden="true"></span>
                        <span class="gallery-corners pointer-events-none absolute inset-4" aria-hidden="true"></span>

                        <span class="gallery-spine absolute inset-0 hidden flex-col items-center justify-between py-5 lg:flex" aria-hidden="true">
                            <span class="font-mono text-xs text-glow-200">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="max-h-[80%] truncate text-sm font-medium text-white [writing-mode:vertical-rl] rotate-180">{{ $item['caption'] }}</span>
                        </span>

                        <span class="gallery-caption absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 bg-linear-to-t from-film-950/95 via-film-950/60 to-transparent p-5 pt-20 sm:p-6 sm:pt-24">
                            <span class="min-w-0">
                                <span class="block font-mono text-xs tracking-widest text-glow-300" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="mt-1.5 block text-base font-semibold text-balance text-white sm:text-lg">{{ $item['caption'] }}</span>
                            </span>
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/25 backdrop-blur-sm transition group-hover:bg-signal-500 group-hover:text-navy-950 group-focus-visible:bg-signal-500 group-focus-visible:text-navy-950" aria-hidden="true">
                                <x-icon name="expand" class="size-4" />
                            </span>
                        </span>
                        <span class="sr-only">(open larger image)</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($total > 1)
            <div class="mt-5 flex items-center gap-4">
                <p class="font-mono text-sm text-ink-2 tabular-nums" aria-hidden="true"><span class="text-glow-300" data-gallery-current>{{ str_pad($start + 1, 2, '0', STR_PAD_LEFT) }}</span> / {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</p>
                <div class="h-0.5 flex-1 overflow-hidden rounded-full bg-film-600" aria-hidden="true">
                    <div class="h-full rounded-full bg-glow-400 transition-[width] duration-500" style="width: {{ ($start + 1) / $total * 100 }}%" data-gallery-progress></div>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="flex size-11 items-center justify-center rounded-full text-ink ring-1 ring-film-500 transition hover:bg-signal-500 hover:text-navy-950 hover:ring-signal-500" data-gallery-prev>
                        <x-icon name="chevron-left" class="size-5" /><span class="sr-only">Previous photo</span>
                    </button>
                    <button type="button" class="flex size-11 items-center justify-center rounded-full text-ink ring-1 ring-film-500 transition hover:bg-signal-500 hover:text-navy-950 hover:ring-signal-500" data-gallery-next>
                        <x-icon name="chevron-right" class="size-5" /><span class="sr-only">Next photo</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

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
