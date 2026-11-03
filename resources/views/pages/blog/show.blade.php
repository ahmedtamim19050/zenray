@php
    $shareUrl = urlencode(route('blog.show', $post));
    $shareTitle = rawurlencode($post->title);
    $share = 'flex size-11 items-center justify-center rounded-full bg-film-800 text-ink-2 ring-1 ring-film-600 transition-colors hover:bg-film-700 hover:text-ink';
@endphp

<x-layouts.app :title="$post->title" :description="$post->meta_description ?: $post->summary(160)"
               :image="\App\Support\Photo::url($post->photo)" :noindex="! $post->isLive()">
    @unless ($post->isLive())
        <div class="border-b border-signal-500/30 bg-signal-500/10 px-4 py-3 text-center text-sm text-signal-text" role="note">
            Preview: this post is <strong>{{ strtolower($post->state()[0]) }}</strong> and only visible to admins.
            <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold underline underline-offset-4">Edit post</a>
        </div>
    @endunless

    <article>
        <header class="border-b border-film-600 bg-film-900">
            <div class="mx-auto max-w-4xl px-4 pt-14 pb-10 sm:px-6 sm:pt-16 lg:px-8">
                <nav aria-label="Breadcrumb" class="hero-in">
                    <ol class="flex flex-wrap items-center gap-2 text-xs text-ink-3">
                        <li><a href="{{ route('home') }}" class="hover:text-ink">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-ink">Blog</a></li>
                        @if ($post->category)
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="font-medium text-ink-2 hover:text-ink">{{ $post->category->name }}</a></li>
                        @endif
                    </ol>
                </nav>

                <h1 class="hero-in mt-5 text-3xl leading-tight font-semibold tracking-tight text-balance text-ink sm:text-4xl">{{ $post->title }}</h1>

                @if ($post->excerpt)
                    <p class="hero-in mt-6 max-w-2xl text-lg leading-relaxed text-ink-2 sm:text-xl">{{ $post->excerpt }}</p>
                @endif

                <p class="hero-in mt-10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-ink-3">
                    @if ($post->author)
                        <span class="flex items-center gap-2">
                            <span class="flex size-8 items-center justify-center rounded-full bg-glow-400/15 font-sans text-sm font-bold text-glow-100 ring-1 ring-glow-400/30" aria-hidden="true">{{ Str::upper(Str::substr($post->author->name, 0, 1)) }}</span>
                            <span class="font-sans text-sm font-semibold text-ink">{{ $post->author->name }}</span>
                        </span>
                    @endif
                    @if ($post->published_at)
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
                    @endif
                    <span>{{ $post->readingMinutes() }} min read</span>
                </p>
            </div>

            @if ($post->photo)
                <div class="mx-auto max-w-5xl px-4 pb-10 sm:px-6 lg:px-8">
                    <x-photo :name="$post->photo" :alt="$post->alt ?: $post->title" priority sizes="(min-width: 1024px) 1000px, 100vw" class="aspect-[16/9] rounded-2xl ring-1 ring-film-600" />
                </div>
            @endif
        </header>

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-12 lg:px-8">
            <div class="prose-xray lg:col-span-8">
                {!! $post->bodyHtml() !!}
            </div>

            <aside class="lg:col-span-4" aria-label="Share and next steps">
                <div class="space-y-6 lg:sticky lg:top-28">
                    <div>
                        <p class="text-sm font-semibold text-ink">Share</p>
                        <ul class="mt-3 flex gap-2">
                            <li>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="{{ $share }}">
                                    <x-social-icon network="linkedin" class="size-4" /><span class="sr-only">Share on LinkedIn (opens in a new tab)</span>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="{{ $share }}">
                                    <x-social-icon network="facebook" class="size-4" /><span class="sr-only">Share on Facebook (opens in a new tab)</span>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:?subject={{ $shareTitle }}&amp;body={{ $shareUrl }}" class="{{ $share }}">
                                    <x-icon name="mail" class="size-4" /><span class="sr-only">Share by email</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl bg-film-800 p-6 ring-1 ring-film-600">
                        <p class="font-bold text-ink">Need help choosing equipment?</p>
                        <p class="mt-1 text-sm leading-relaxed text-ink-2">Tell us about your facility and our team will talk you through the options.</p>
                        <x-button :href="route('quote')" size="sm" icon="arrow-right" class="mt-5 w-full">Request a Quote</x-button>
                    </div>
                </div>
            </aside>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-film-600 py-14 sm:py-20" aria-labelledby="related-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <x-section-heading dark id="related-heading" eyebrow="Keep reading" title="More from the blog" />
                    <x-button :href="route('blog.index')" variant="outline-dark" icon="arrow-right" class="self-start sm:self-auto">All articles</x-button>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                    @foreach ($related as $item)
                        <x-cards.post :post="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
