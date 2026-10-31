@php
    $services = config('zenray.services');
    $process = [
        ['title' => 'Tell us what you need', 'text' => 'Share your equipment, room and the support you are looking for.'],
        ['title' => 'We discuss the details', 'text' => 'Our team asks the right questions and explains what is involved.'],
        ['title' => 'Plan and quotation', 'text' => 'You receive a proposal that matches your requirements.'],
        ['title' => 'Ongoing support', 'text' => 'We stay available for questions and follow-up help.'],
    ];
    $faqs = [
        ['Do I need to know the exact equipment model?', 'No. Describe what you have or what you need in your own words, and our team will help identify the details.'],
        ['Can you help with equipment you did not buy from ZenRay?', 'Tell us about the equipment and the help you need, and our team will let you know what we can offer.'],
        ['How do I request a service?', 'Use the Request a Quote form or contact us directly. If you have an account, you can follow the progress of your request from your dashboard.'],
        ['Is there a charge for a first conversation?', 'Contact our team to discuss your needs. Any costs are confirmed with you in a quotation before work begins.'],
    ];
@endphp

<x-layouts.app title="Services" description="Equipment installation, maintenance and technical support, inspection and technical consultation from ZenRay Medical Systems.">
    <x-page-hero eyebrow="Services" title="Support for your equipment, from set-up onwards"
                 description="Installation, maintenance, inspection and consultation, explained in plain language with a clear next step."
                 photo="technician" photo-alt="A technician checking connections on medical equipment">
        <x-button :href="route('quote', ['interest' => 'maintenance'])" size="lg" icon="arrow-right" class="btn-shine">Request a service</x-button>
        <x-button href="#process" variant="outline-light" size="lg" class="bg-white/5">How it works</x-button>
    </x-page-hero>

    <section class="py-20 sm:py-28" aria-labelledby="services-list-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading dark id="services-list-heading" eyebrow="What we do" title="Four ways our team can help" />
            <div class="mt-14 space-y-5" data-stagger>
                @foreach ($services as $service)
                    <article id="{{ $service['slug'] }}" data-spotlight class="group grid scroll-mt-28 gap-6 rounded-3xl bg-film-800 p-6 ring-1 ring-film-600 transition duration-300 hover:shadow-card-hover sm:p-8 lg:grid-cols-12 lg:items-center">
                        <div class="flex items-center gap-5 lg:col-span-4">
                            <span class="font-mono text-4xl font-bold text-film-600 transition-colors group-hover:text-signal-500" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <span class="flex size-12 items-center justify-center rounded-2xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25 transition-colors group-hover:bg-signal-500 group-hover:text-navy-950"><x-icon :name="$service['icon']" class="size-6" /></span>
                            </div>
                            <h2 class="text-xl font-bold text-ink">{{ $service['name'] }}</h2>
                        </div>
                        <p class="text-[0.9375rem] leading-relaxed text-ink-2 lg:col-span-4">{{ $service['summary'] }}</p>
                        <div class="lg:col-span-4 lg:text-right">
                            <p class="text-sm text-ink-3"><span class="font-semibold text-ink">Next step:</span> {{ $service['next_step'] }}</p>
                            <a href="{{ route('contact', ['interest' => $service['slug']]) }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-ink hover:text-signal-300">
                                {{ $service['action'] }} <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="process" class="relative isolate scroll-mt-24 overflow-hidden bg-film-950 py-20 text-white sm:py-28" aria-labelledby="process-heading">
        <div class="hero-aurora pointer-events-none absolute -top-40 -right-40 -z-10 size-[34rem] rounded-full bg-signal-500/20 blur-[120px]" aria-hidden="true"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading id="process-heading" eyebrow="How it works" title="A clear process from first contact" dark>
                <x-slot:description>A typical journey. Timings depend on your equipment and requirements.</x-slot:description>
            </x-section-heading>
            <ol class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-stagger>
                @foreach ($process as $step)
                    <li class="relative rounded-3xl bg-white/[0.04] p-7 ring-1 ring-white/10">
                        <span class="flex size-12 items-center justify-center rounded-full bg-signal-500 font-mono font-bold text-navy-950">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-6 text-lg font-bold">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-[0.9375rem] leading-relaxed text-navy-200">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="py-20 sm:py-28" aria-labelledby="faq-heading">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
            <div class="lg:col-span-4">
                <x-section-heading dark id="faq-heading" eyebrow="Questions" title="Frequently asked" />
                <x-button :href="route('contact')" variant="outline-dark" icon="arrow-right" class="mt-8">Ask something else</x-button>
            </div>
            <div class="space-y-3 lg:col-span-8" data-stagger>
                @foreach ($faqs as [$question, $answer])
                    <details class="group rounded-2xl bg-film-800 ring-1 ring-film-600 open:shadow-card" @if ($loop->first) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 font-semibold text-ink sm:p-6 [&::-webkit-details-marker]:hidden">
                            {{ $question }}
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-film-700 text-ink transition group-open:rotate-45 group-open:bg-signal-500"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg></span>
                        </summary>
                        <p class="px-5 pb-6 leading-relaxed text-ink-2 sm:px-6">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
