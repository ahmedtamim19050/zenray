@props(['title', 'description', 'updated'])

<x-layouts.app :title="$title" :description="$description">
    <x-page-hero :eyebrow="$title" :title="$title" :description="'Last updated '.$updated" />

    <section class="py-14 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (auth()->user()?->isAdmin())
                <div class="mb-10 rounded-2xl bg-signal-500/10 p-7 text-sm text-ink ring-1 ring-signal-500/30" role="note">
                    <p class="font-semibold">Visible to admins only: this page is a general template.</p>
                    <p class="mt-1">Please have it reviewed by a legal adviser and adjusted for your jurisdiction before relying on it. Edit it in <code class="rounded bg-film-700 px-1">resources/views/pages/{{ Str::slug($title) === 'privacy-policy' ? 'privacy' : 'terms' }}.blade.php</code>.</p>
                </div>
            @endif
            <div class="legal">{{ $slot }}</div>
        </div>
    </section>
</x-layouts.app>
