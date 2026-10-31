{{-- Inner-page hero: dark stage, breadcrumb, split title, optional photo + actions slot. --}}
@props(['eyebrow', 'title', 'description' => null, 'photo' => null, 'photoAlt' => ''])

<section class="relative isolate overflow-hidden border-b border-glow-400/10 bg-film-950 text-white" aria-labelledby="page-heading">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="hero-aurora absolute -top-40 -right-32 size-[38rem] rounded-full bg-glow-500/20 blur-[140px]"></div>
        <div class="hero-aurora is-alt absolute -bottom-48 -left-40 size-[32rem] rounded-full bg-signal-500/10 blur-[140px]"></div>
        <div class="bg-grid absolute inset-0 [mask-image:radial-gradient(ellipse_70%_70%_at_40%_40%,black,transparent)]"></div>
    </div>

    <div @class(['mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24', 'lg:grid-cols-12' => $photo])>
        <div @class(['lg:col-span-7' => $photo, 'max-w-3xl' => ! $photo])>
            <nav aria-label="Breadcrumb" class="hero-in" style="--d: .05s">
                <ol class="flex items-center gap-2 font-mono text-xs tracking-wider text-ink-3 uppercase">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-glow-300">{{ $eyebrow }}</li>
                </ol>
            </nav>
            <h1 id="page-heading" data-split class="text-glow mt-6 text-[2.5rem] leading-[1.03] font-bold tracking-[-0.04em] text-balance sm:text-6xl">{{ $title }}</h1>
            @if ($description)
                <p class="hero-in mt-6 max-w-2xl text-lg leading-relaxed text-ink-2 sm:text-xl" style="--d: .4s">{{ $description }}</p>
            @endif
            @if ($slot->isNotEmpty())
                <div class="hero-in mt-9 flex flex-col gap-3 sm:flex-row" style="--d: .55s">{{ $slot }}</div>
            @endif
        </div>

        @if ($photo)
            <div class="lg:col-span-5">
                <div data-tilt="4" class="hero-media relative rounded-[2rem] bg-film-900 p-2 shadow-[0_40px_90px_-30px_rgb(0_0_0/0.9),0_0_70px_-30px_rgb(46_199_240/0.5)] ring-1 ring-glow-400/20">
                    <x-xray :name="$photo" :alt="$photoAlt" base="xray" scan priority sizes="(min-width: 1024px) 480px, 100vw" class="aspect-[4/3] rounded-[1.5rem]">
                        <span class="absolute top-3 left-3 flex size-7 items-center justify-center rounded border border-glow-200/70 font-mono text-xs font-bold text-glow-100" aria-hidden="true">R</span>
                    </x-xray>
                    <div class="hud-corners pointer-events-none absolute inset-4" aria-hidden="true"></div>
                </div>
            </div>
        @endif
    </div>
</section>
