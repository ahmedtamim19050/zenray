{{-- Bold CTA band + contact section with the working enquiry form --}}
@php
    $contact = config('zenray.contact');
    $contactRows = array_filter([
        ['phone', 'Call us', $contact['phone'], $contact['phone'] ? 'tel:'.preg_replace('/[^\d+]/', '', $contact['phone']) : null],
        ['mail', 'Email us', $contact['email'], $contact['email'] ? 'mailto:'.$contact['email'] : null],
        ['map-pin', 'Visit us', $contact['address'], $contact['map_url'] ?? null],
        ['clock', 'Business hours', $contact['hours'], null],
    ], fn (array $row) => filled($row[2]));
    $steps = [
        ['title' => 'Tell us what you need', 'text' => 'Send the form, even if you are not sure of exact product names.'],
        ['title' => 'Discuss your options', 'text' => 'Our team talks through suitable equipment and services with you.'],
        ['title' => 'Receive a quotation', 'text' => 'We prepare a quotation based on what you need.'],
    ];
@endphp

{{-- CTA band --}}
<section class="bg-navy-800" aria-labelledby="cta-heading">
    <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="max-w-2xl">
            <h2 id="cta-heading" data-reveal class="text-2xl leading-tight font-semibold tracking-tight text-balance text-white sm:text-3xl">
                Looking for the Right Imaging Solution?
            </h2>
            <p class="mt-4 text-lg text-navy-100">Tell us what your facility needs, and our team can discuss the available options with you.</p>
        </div>
        <div class="flex shrink-0 flex-col gap-3 sm:flex-row" data-reveal>
            <x-button href="#enquiry" size="lg" icon="arrow-right">Request a Quote</x-button>
            <x-button :href="route('contact')" variant="outline-light" size="lg">Contact Our Team</x-button>
        </div>
    </div>
</section>

{{-- Contact panel: navy photo side with the details and next steps, joined to the form --}}
<section id="contact" class="scroll-mt-24 bg-film-900 py-16 sm:py-24" aria-labelledby="contact-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid overflow-hidden rounded-3xl bg-film-800 shadow-card-hover ring-1 ring-film-600 lg:grid-cols-12" data-reveal>
            <div class="relative isolate flex flex-col bg-navy-800 p-7 text-white sm:p-10 lg:col-span-5">
                <x-photo name="room-overview" alt="" sizes="(min-width: 1024px) 500px, 100vw" class="absolute inset-0 -z-20 opacity-50 mix-blend-luminosity" />
                <span class="absolute inset-0 -z-10 bg-linear-to-b from-navy-800/85 via-navy-800/55 via-45% to-navy-900" aria-hidden="true"></span>

                <p class="text-sm font-semibold text-signal-300">Get in touch</p>
                <h2 id="contact-heading" class="mt-3 text-3xl leading-tight font-semibold tracking-tight text-balance sm:text-4xl">Let’s talk about your facility</h2>
                <p class="mt-4 text-base leading-relaxed text-navy-100">No technical knowledge needed. Tell us what you are looking for and we will take it from there.</p>

                {{-- Only the details set in Admin → Settings are shown --}}
                @if ($contactRows)
                    <ul class="mt-8 space-y-4">
                        @foreach ($contactRows as [$icon, $label, $value, $link])
                            <li>
                                <{{ $link ? 'a href='.$link : 'div' }} class="group flex items-center gap-3.5">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-signal-300 ring-1 ring-white/20 transition-colors duration-300 group-hover:bg-signal-500 group-hover:text-navy-950">
                                        <x-icon :name="$icon" class="size-5" />
                                    </span>
                                    <span class="min-w-0 break-words">
                                        <span class="block text-sm text-navy-200">{{ $label }}</span>
                                        <span class="font-semibold"><span @class(['link-draw' => $link])>{{ $value }}</span></span>
                                    </span>
                                </{{ $link ? 'a' : 'div' }}>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-auto pt-10">
                    <h3 class="text-sm font-semibold text-navy-100">What happens next</h3>
                    <ol class="mt-4 space-y-4">
                        @foreach ($steps as $step)
                            <li class="flex gap-3.5">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-signal-500 text-sm font-semibold text-navy-950">{{ $loop->iteration }}</span>
                                <span>
                                    <span class="block font-semibold">{{ $step['title'] }}</span>
                                    <span class="mt-0.5 block text-sm leading-relaxed text-navy-100">{{ $step['text'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <div id="enquiry" class="scroll-mt-24 p-6 sm:p-10 lg:col-span-7 lg:p-12">
                <x-enquiry-form type="quote" title="Send us your request" bare />
            </div>
        </div>
    </div>
</section>
