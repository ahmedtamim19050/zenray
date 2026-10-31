{{-- Bold CTA band + contact section with the working enquiry form --}}
@php
    $contact = config('zenray.contact');
    $steps = [
        ['title' => 'Tell us what you need', 'text' => 'Send the form, even if you are not sure of exact product names.'],
        ['title' => 'Discuss your options', 'text' => 'Our team talks through suitable equipment and services with you.'],
        ['title' => 'Receive a quotation', 'text' => 'We prepare a quotation based on what you need.'],
    ];
@endphp

{{-- CTA band --}}
<section class="relative isolate overflow-hidden bg-signal-500" aria-labelledby="cta-heading">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -top-24 -right-10 size-96 rounded-full bg-signal-300/60 blur-3xl"></div>
        <img src="{{ asset('images/zenray-mark.svg') }}" alt="" width="160" height="112" loading="lazy" class="absolute -right-10 -bottom-16 w-[28rem] opacity-[0.12] brightness-0">
    </div>
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-16 sm:px-6 sm:py-20 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="max-w-2xl">
            <h2 id="cta-heading" data-split class="text-[2.25rem] leading-[1.04] font-bold tracking-[-0.035em] text-balance text-navy-950 sm:text-5xl">
                Looking for the Right Imaging Solution?
            </h2>
            <p class="mt-4 text-lg text-navy-900/80">Tell us what your facility needs, and our team can discuss the available options with you.</p>
        </div>
        <div class="flex shrink-0 flex-col gap-3 sm:flex-row" data-reveal>
            <x-button href="#enquiry" variant="glow" size="lg" icon="arrow-right" class="btn-shine">Request a Quote</x-button>
            <x-button :href="route('contact')" variant="outline" size="lg" class="border-navy-950/20 bg-white/30 hover:bg-film-800">Contact Our Team</x-button>
        </div>
    </div>
</section>

{{-- Contact + form --}}
<section id="contact" class="relative isolate scroll-mt-24 bg-film-900 py-24 sm:py-32" aria-labelledby="contact-heading">
    <div class="bg-grid-dark pointer-events-none absolute inset-0 -z-10 [mask-image:linear-gradient(to_bottom,black,transparent_50%)]" aria-hidden="true"></div>

    <div class="mx-auto grid max-w-7xl gap-14 px-4 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8">
        <div class="lg:col-span-5">
            <x-section-heading dark id="contact-heading" eyebrow="Get in touch" title="Let’s talk about your facility">
                <x-slot:description>
                    No technical knowledge needed. Tell us what you are looking for and we will take it from there.
                </x-slot:description>
            </x-section-heading>

            <ul class="mt-10 space-y-3" data-stagger>
                @foreach ([['phone', 'Call us', $contact['phone'], $contact['phone'] ? 'tel:'.preg_replace('/[^\d+]/', '', $contact['phone']) : null], ['mail', 'Email us', $contact['email'], $contact['email'] ? 'mailto:'.$contact['email'] : null], ['map-pin', 'Visit us', $contact['address'], null], ['clock', 'Business hours', $contact['hours'], null]] as [$icon, $label, $value, $link])
                    <li class="flex items-center gap-4 rounded-2xl bg-film-800 p-4 ring-1 ring-film-600">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25">
                            <x-icon :name="$icon" class="size-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold tracking-wide text-ink-3 uppercase">{{ $label }}</span>
                            @if ($value && $link)
                                <a href="{{ $link }}" class="block truncate text-lg font-bold text-ink hover:text-signal-300">{{ $value }}</a>
                            @elseif ($value)
                                <span class="block text-lg font-bold text-ink">{{ $value }}</span>
                            @else
                                <span class="block text-[0.9375rem] text-ink-3 italic">Pending configuration</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>

            <ol class="mt-10 space-y-6 border-l-2 border-film-600 pl-6" aria-label="What happens next">
                @foreach ($steps as $step)
                    <li class="relative">
                        <span class="absolute top-0 -left-[2.35rem] flex size-6 items-center justify-center rounded-full bg-signal-500 font-mono text-xs font-bold text-navy-950 ring-4 ring-film-900">{{ $loop->iteration }}</span>
                        <p class="font-bold text-ink">{{ $step['title'] }}</p>
                        <p class="mt-1 text-[0.9375rem] text-ink-2">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>

        <div id="enquiry" class="scroll-mt-24 lg:col-span-7" data-reveal>
            <div class="border-glow rounded-[1.25rem]">
                <x-enquiry-form type="quote" />
            </div>
        </div>
    </div>
</section>
