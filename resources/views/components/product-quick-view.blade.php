{{-- Quick view dialog, filled from a product card's data-* attributes by app.js. --}}
<dialog class="m-auto w-[min(56rem,calc(100vw-2rem))] max-w-none rounded-3xl bg-film-800 p-0 shadow-2xl backdrop:bg-navy-950/70 backdrop:backdrop-blur-sm"
        aria-labelledby="quick-view-title" data-quick-view-dialog>
    <div class="grid md:grid-cols-2">
        <div class="relative bg-film-700">
            <img src="" alt="" class="aspect-[4/3] size-full object-cover md:aspect-auto md:h-full" data-qv-image>
        </div>
        <div class="flex flex-col p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <p class="text-xs font-semibold tracking-wide text-signal-400 uppercase" data-qv-category></p>
                <button type="button" class="-mt-2 -mr-2 flex size-10 shrink-0 items-center justify-center rounded-full text-ink hover:bg-film-700" data-qv-close autofocus>
                    <x-icon name="close" class="size-5" /><span class="sr-only">Close</span>
                </button>
            </div>
            <h2 id="quick-view-title" class="text-2xl font-bold text-ink" data-qv-name></h2>
            <p class="mt-3 leading-relaxed text-ink-2" data-qv-summary></p>

            <div class="mt-5 rounded-xl bg-film-900 p-4 text-sm text-ink-2" data-qv-example>
                <p class="font-semibold text-ink">This is an example listing</p>
                <p class="mt-1">It shows the type of equipment we can help with. Specifications, availability and pricing depend on the exact model. Ask our team for details.</p>
            </div>

            <div class="mt-auto grid gap-2 pt-8 sm:grid-cols-2">
                <a href="#" class="inline-flex h-12 items-center justify-center rounded-full bg-signal-500 px-5 font-semibold text-navy-950 transition hover:bg-signal-400" data-qv-quote>Request a Quote</a>
                <a href="{{ route('contact') }}" class="inline-flex h-12 items-center justify-center rounded-full border border-film-600 px-5 font-semibold text-ink transition hover:border-glow-400">Ask a question</a>
            </div>
        </div>
    </div>
</dialog>
