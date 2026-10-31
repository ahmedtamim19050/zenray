@php
    $isQuote = $type === 'quote';
    $contact = config('zenray.contact');
@endphp

<x-layouts.app :title="$isQuote ? 'Request a Quote' : 'Contact Us'"
               :description="$isQuote ? 'Request a quotation for medical imaging equipment or technical services from ZenRay Medical Systems.' : 'Contact the ZenRay Medical Systems team about imaging equipment, installation and technical support.'">
    <section class="bg-film-900" aria-labelledby="page-heading">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-12 lg:gap-16 lg:px-8">
            <div class="lg:col-span-5">
                <p class="inline-flex items-center gap-2 text-sm font-semibold text-glow-300">
                    <span class="h-px w-8 bg-signal-500" aria-hidden="true"></span>
                    {{ $isQuote ? 'Quotation' : 'Contact' }}
                </p>
                <h1 id="page-heading" class="mt-5 text-4xl font-bold tracking-tight text-ink sm:text-5xl">
                    {{ $isQuote ? 'Request a Quote' : 'Talk to Our Team' }}
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-ink-2">
                    {{ $isQuote
                        ? 'Tell us what your facility needs. You do not need exact product names or technical details. Our team can help with that.'
                        : 'Have a question about equipment or services? Send us a message and our team will get back to you.' }}
                </p>

                <dl class="mt-10 space-y-5">
                    @foreach ([['mail', 'Email', $contact['email']], ['phone', 'Phone', $contact['phone']], ['map-pin', 'Address', $contact['address']], ['clock', 'Business hours', $contact['hours']]] as [$icon, $label, $value])
                        <div class="flex gap-4">
                            <dt class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-film-800 text-glow-300 ring-1 ring-film-600">
                                <x-icon :name="$icon" class="size-5" /><span class="sr-only">{{ $label }}</span>
                            </dt>
                            <dd class="pt-2 text-[0.9375rem]">
                                @if ($value)
                                    <span class="text-ink">{{ $value }}</span>
                                @else
                                    <span class="text-ink-3 italic">{{ $label }}: pending configuration</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div id="enquiry" class="scroll-mt-24 lg:col-span-7">
                <x-enquiry-form :type="$type" />
            </div>
        </div>
    </section>
</x-layouts.app>
