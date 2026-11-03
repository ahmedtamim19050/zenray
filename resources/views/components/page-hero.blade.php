{{-- Inner-page hero: breadcrumb, title, optional photo + actions slot. --}}
@props(['eyebrow', 'title', 'description' => null, 'photo' => null, 'photoAlt' => ''])

<section class="border-b border-film-600 bg-film-900" aria-labelledby="page-heading">
    <div @class(['mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:px-8', 'lg:grid-cols-12' => $photo])>
        <div @class(['lg:col-span-7' => $photo, 'max-w-3xl' => ! $photo])>
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-ink-3">
                    <li><a href="{{ route('home') }}" class="underline-offset-4 hover:text-ink hover:underline">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-medium text-ink-2">{{ $eyebrow }}</li>
                </ol>
            </nav>
            <h1 id="page-heading" class="hero-in mt-5 text-3xl leading-tight font-semibold tracking-tight text-balance text-ink sm:text-4xl">{{ $title }}</h1>
            @if ($description)
                <p class="hero-in mt-4 max-w-2xl text-base leading-relaxed text-ink-2">{{ $description }}</p>
            @endif
            @if ($slot->isNotEmpty())
                <div class="hero-in mt-6 flex flex-col gap-3 sm:flex-row">{{ $slot }}</div>
            @endif
        </div>

        @if ($photo)
            <div class="hero-in lg:col-span-5">
                <x-photo :name="$photo" :alt="$photoAlt" priority sizes="(min-width: 1024px) 480px, 100vw" class="aspect-[4/3] rounded-2xl ring-1 ring-film-600" />
            </div>
        @endif
    </div>
</section>
