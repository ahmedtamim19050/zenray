<x-layouts.app :title="$project->title" :description="$project->summary">
    <x-page-hero eyebrow="Projects" :title="$project->title" :description="$project->summary"
                 :photo="$project->photo" :photo-alt="$project->alt ?: $project->title">
        <x-button :href="route('quote')" size="lg" icon="arrow-right" class="btn-shine">Discuss a similar project</x-button>
    </x-page-hero>

    <section class="py-16 sm:py-24" aria-label="Project details">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
            <aside class="lg:col-span-4">
                <dl class="sticky top-28 space-y-px overflow-hidden rounded-2xl bg-film-800 font-mono text-sm ring-1 ring-film-600">
                    @foreach (array_filter(['Delivered' => $project->delivered, 'Location' => $project->location, 'Completed' => $project->completed_on?->format('F Y')]) as $label => $value)
                        <div class="bg-film-900/60 px-5 py-4">
                            <dt class="text-xs tracking-widest text-glow-300 uppercase">{{ $label }}</dt>
                            <dd class="mt-1 font-sans text-[0.9375rem] font-semibold text-ink">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </aside>

            <div class="lg:col-span-8">
                @if (filled($project->body))
                    <div class="prose-xray">{!! Str::markdown($project->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                @else
                    <p class="text-lg leading-relaxed text-ink-2">{{ $project->summary }}</p>
                @endif
            </div>
        </div>
    </section>

    @if ($more->isNotEmpty())
        <section class="border-t border-glow-400/10 py-16 sm:py-24" aria-labelledby="more-projects-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-section-heading dark id="more-projects-heading" eyebrow="More projects" title="Other work we have delivered" />
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3" data-stagger>
                    @foreach ($more as $item)
                        <x-cards.project :project="['title' => $item->title, 'slug' => $item->slug, 'photo' => $item->photo, 'image_alt' => $item->alt, 'delivered' => $item->delivered, 'location' => $item->location, 'description' => $item->summary]" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
