{{--
    Quote / contact form. Posts to EnquiryController, which emails the team.
    Pre-select an interest with ?interest={slug} (e.g. from product cards).
    Pass a title to show a heading above the fields, and bare when a parent supplies the card.
--}}
@props(['type' => 'quote', 'title' => null, 'bare' => false])

@php
    $options = \App\Http\Requests\StoreEnquiryRequest::interestOptions();
    $selected = old('interest', array_key_exists((string) request('interest'), $options) ? request('interest') : '');
    $status = session('enquiry_status');
    $contact = config('zenray.contact');
    $card = $bare ? '' : 'rounded-2xl bg-film-800 p-6 shadow-card ring-1 ring-film-600 sm:p-8';
    $user = auth()->user();
@endphp

<div {{ $attributes }}>
    @if ($status === 'sent')
        <div class="{{ $card }} text-center" role="status" tabindex="-1" data-autofocus>
            <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-400/10 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-400/30">
                <x-icon name="check" class="size-7" />
            </span>
            <h3 class="mt-5 text-xl font-bold text-ink">Thank you, your message has been sent</h3>
            <p class="mt-2 text-ink-2">Our team will review it and reply using the details you provided.</p>
            <a href="{{ url()->current() }}#enquiry" class="mt-6 inline-flex text-sm font-semibold text-ink underline underline-offset-4 hover:text-ink">Send another message</a>
        </div>
    @else
        <form method="POST" action="{{ route('enquiries.store') }}" class="{{ $card }} space-y-6" novalidate data-enquiry-form>
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            @if ($title)
                <h3 class="text-2xl font-semibold tracking-tight text-ink">{{ $title }}</h3>
            @endif

            {{-- Honeypot field, hidden from people and assistive technology --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="enquiry-website">Website</label>
                <input type="text" id="enquiry-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($status === 'failed')
                <div class="rounded-xl bg-red-500/10 p-4 text-sm text-red-800 dark:text-red-200 ring-1 ring-red-400/30" role="alert" tabindex="-1" data-autofocus>
                    <p class="font-semibold">Sorry, we could not send your message.</p>
                    <p class="mt-1">Please try again in a moment.
                        @if ($contact['email']) You can also email us at <a href="mailto:{{ $contact['email'] }}" class="underline">{{ $contact['email'] }}</a>. @endif
                    </p>
                </div>
            @elseif ($errors->any())
                <div class="rounded-xl bg-red-500/10 p-4 text-sm text-red-800 dark:text-red-200 ring-1 ring-red-400/30" role="alert" tabindex="-1" data-autofocus>
                    <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
                </div>
            @endif

            {{-- Tap-to-select choices. Each is a real radio button, so keyboards and screen readers work as usual. --}}
            <fieldset @error('interest') aria-describedby="enquiry-interest-error" @enderror>
                <legend class="text-sm font-semibold text-ink">What are you interested in? <span class="text-red-700 dark:text-red-300" aria-hidden="true">*</span></legend>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($options as $value => $name)
                        <label class="relative inline-flex min-h-11 cursor-pointer items-center rounded-full bg-film-900 px-4 py-2 text-sm font-medium text-ink-2 ring-1 ring-film-500 transition-colors duration-200 outline-glow-500 hover:ring-ink-3 hover:text-ink has-checked:bg-signal-500 has-checked:font-semibold has-checked:text-navy-950 has-checked:ring-signal-500 has-focus-visible:outline-3 has-focus-visible:outline-offset-2">
                            <input type="radio" name="interest" value="{{ $value }}" class="peer sr-only" required @checked($selected === $value)>
                            <span class="w-0 overflow-hidden opacity-0 transition-all duration-200 ease-calm peer-checked:mr-1.5 peer-checked:w-4 peer-checked:opacity-100 motion-reduce:transition-none" aria-hidden="true">
                                <x-icon name="check" class="size-4!" />
                            </span>
                            {{ $name }}
                        </label>
                    @endforeach
                </div>
                @error('interest') <p id="enquiry-interest-error" class="mt-2 text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.floating name="name" label="Your name" :value="$user?->name" required autocomplete="name" />
                <x-form.floating name="organization" label="Organization or facility" :value="$user?->organization" autocomplete="organization" />
                <x-form.floating name="email" label="Email" type="email" :value="$user?->email" describedby="enquiry-contact-hint" autocomplete="email" inputmode="email" />
                <x-form.floating name="phone" label="Phone" type="tel" :value="$user?->phone" describedby="enquiry-contact-hint" autocomplete="tel" inputmode="tel" />
                <p id="enquiry-contact-hint" class="-mt-1 text-sm text-ink-3 sm:col-span-2">Please give us at least an email or a phone number.</p>
            </div>

            <div>
                <x-form.floating name="message" label="How can we help?" type="textarea" required maxlength="3000" describedby="enquiry-message-hint" />
                <p id="enquiry-message-hint" class="mt-1.5 text-sm text-ink-3">For example: the type of facility, the equipment you are looking for, and any timing or space details.</p>
            </div>

            <div>
                <button type="submit" class="group flex min-h-14 w-full items-center justify-center gap-2 rounded-full bg-signal-500 px-7 font-semibold text-navy-950 transition-colors duration-200 hover:bg-signal-400 disabled:cursor-wait disabled:opacity-70">
                    <span data-submit-label>{{ $type === 'quote' ? 'Send quote request' : 'Send message' }}</span>
                    <x-icon name="arrow-right" class="size-5 transition-transform duration-300 ease-calm group-hover:translate-x-1" />
                </button>
                <p class="mt-3 text-center text-sm text-ink-3">
                    We use these details only to reply to you. See our <a href="{{ route('privacy') }}" class="underline underline-offset-2 hover:text-ink">Privacy Policy</a>.
                </p>
            </div>
        </form>
    @endif
</div>
