<x-layouts.app title="Image Credits" description="Credits and licence information for photography used on the ZenRay Medical Systems website.">
    <section class="bg-film-900" aria-labelledby="page-heading">
        <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <h1 id="page-heading" class="text-3xl font-bold tracking-tight text-ink sm:text-4xl">Image Credits</h1>
            <p class="mt-5 max-w-2xl text-lg text-ink-2">
                Some photographs on this website are used under open licences from Wikimedia Commons.
                They show typical equipment and do not represent specific products sold by {{ config('zenray.name') }}
                or any endorsement by the photographers or manufacturers.
            </p>
            <p class="mt-3 max-w-2xl text-ink-2">
                Adapted versions of images licensed CC BY-SA are shared under the same licence.
            </p>

            <ul class="mt-10 space-y-4">
                @foreach (config('zenray.photo_credits') as $credit)
                    <li class="rounded-2xl bg-film-800 p-6 ring-1 ring-film-600">
                        <h2 class="font-bold text-ink">
                            <a href="{{ $credit['source_url'] }}" class="underline decoration-film-500 underline-offset-4 hover:decoration-ink" rel="noopener" target="_blank">
                                {{ $credit['title'] }}<span class="sr-only"> (opens in a new tab)</span>
                            </a>
                        </h2>
                        <dl class="mt-3 grid gap-x-6 gap-y-1.5 text-sm sm:grid-cols-[8rem_1fr]">
                            <dt class="font-medium text-ink-3">Author</dt>
                            <dd class="text-ink-2"><bdi>{{ $credit['author'] }}</bdi></dd>
                            <dt class="font-medium text-ink-3">Licence</dt>
                            <dd class="text-ink-2">
                                @if ($credit['license_url'])
                                    <a href="{{ $credit['license_url'] }}" class="underline decoration-film-500 underline-offset-4 hover:decoration-ink" rel="noopener license" target="_blank">{{ $credit['license'] }}<span class="sr-only"> (opens in a new tab)</span></a>
                                @else
                                    {{ $credit['license'] }}
                                @endif
                            </dd>
                            <dt class="font-medium text-ink-3">Changes</dt>
                            <dd class="text-ink-2">{{ $credit['changes'] }}</dd>
                        </dl>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.app>
