{{--
    Quote / contact form. Posts to EnquiryController, which emails the team.
    Pre-select an interest with ?interest={slug} (e.g. from product cards).
--}}
@props(['type' => 'quote'])

@php
    $options = \App\Http\Requests\StoreEnquiryRequest::interestOptions();
    $selected = old('interest', array_key_exists((string) request('interest'), $options) ? request('interest') : '');
    $status = session('enquiry_status');
    $contact = config('zenray.contact');

    $field = 'block w-full rounded-xl border-0 bg-film-950/60 px-4 py-3 text-base text-ink shadow-sm ring-1 ring-film-600 placeholder:text-ink-3 transition focus:ring-2 focus:ring-glow-400 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600';
    $label = 'mb-1.5 block text-sm font-semibold text-ink';
@endphp

<div {{ $attributes }}>
    @if ($status === 'sent')
        <div class="rounded-2xl bg-film-800 p-8 text-center ring-1 ring-film-600" role="status" tabindex="-1" data-autofocus>
            <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-400/10 text-emerald-300 ring-1 ring-emerald-400/30">
                <x-icon name="check" class="size-7" />
            </span>
            <h3 class="mt-5 text-xl font-bold text-ink">Thank you, your message has been sent</h3>
            <p class="mt-2 text-ink-2">Our team will review it and reply using the details you provided.</p>
            <a href="{{ url()->current() }}#enquiry" class="mt-6 inline-flex text-sm font-semibold text-ink underline underline-offset-4 hover:text-signal-300">Send another message</a>
        </div>
    @else
        <form method="POST" action="{{ route('enquiries.store') }}" class="space-y-5 rounded-2xl bg-film-800 p-6 shadow-[0_30px_80px_-30px_rgb(0_0_0/0.9)] ring-1 ring-film-600 sm:p-8" novalidate data-enquiry-form>
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- Honeypot field, hidden from people and assistive technology --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="enquiry-website">Website</label>
                <input type="text" id="enquiry-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($status === 'failed')
                <div class="rounded-xl bg-red-500/10 p-4 text-sm text-red-200 ring-1 ring-red-400/30" role="alert" tabindex="-1" data-autofocus>
                    <p class="font-semibold">Sorry, we could not send your message.</p>
                    <p class="mt-1">Please try again in a moment.
                        @if ($contact['email']) You can also email us at <a href="mailto:{{ $contact['email'] }}" class="underline">{{ $contact['email'] }}</a>. @endif
                    </p>
                </div>
            @elseif ($errors->any())
                <div class="rounded-xl bg-red-500/10 p-4 text-sm text-red-200 ring-1 ring-red-400/30" role="alert" tabindex="-1" data-autofocus>
                    <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="enquiry-name" class="{{ $label }}">Your name <span class="text-red-400" aria-hidden="true">*</span></label>
                    <input type="text" id="enquiry-name" name="name" value="{{ old('name', auth()->user()?->name) }}" required autocomplete="name"
                           class="{{ $field }}" @error('name') aria-invalid="true" aria-describedby="enquiry-name-error" @enderror>
                    @error('name') <p id="enquiry-name-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="enquiry-organization" class="{{ $label }}">Organization or facility</label>
                    <input type="text" id="enquiry-organization" name="organization" value="{{ old('organization', auth()->user()?->organization) }}" autocomplete="organization"
                           class="{{ $field }}" @error('organization') aria-invalid="true" aria-describedby="enquiry-organization-error" @enderror>
                    @error('organization') <p id="enquiry-organization-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="enquiry-email" class="{{ $label }}">Email</label>
                    <input type="email" id="enquiry-email" name="email" value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" inputmode="email"
                           class="{{ $field }}" aria-describedby="enquiry-contact-hint @error('email') enquiry-email-error @enderror" @error('email') aria-invalid="true" @enderror>
                    @error('email') <p id="enquiry-email-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="enquiry-phone" class="{{ $label }}">Phone</label>
                    <input type="tel" id="enquiry-phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" autocomplete="tel" inputmode="tel"
                           class="{{ $field }}" aria-describedby="enquiry-contact-hint @error('phone') enquiry-phone-error @enderror" @error('phone') aria-invalid="true" @enderror>
                    @error('phone') <p id="enquiry-phone-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <p id="enquiry-contact-hint" class="-mt-2 text-sm text-ink-3 sm:col-span-2">Please give us at least an email or a phone number.</p>
            </div>

            <div>
                <label for="enquiry-interest" class="{{ $label }}">What are you interested in? <span class="text-red-400" aria-hidden="true">*</span></label>
                <div class="relative">
                <select id="enquiry-interest" name="interest" required class="{{ $field }} appearance-none pr-11"
                        @error('interest') aria-invalid="true" aria-describedby="enquiry-interest-error" @enderror>
                    <option value="" disabled @selected($selected === '')>Choose an option</option>
                    <optgroup label="Equipment">
                        @foreach (config('zenray.product_categories') as $category)
                            <option value="{{ $category['slug'] }}" @selected($selected === $category['slug'])>{{ $category['name'] }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Services">
                        @foreach (config('zenray.services') as $service)
                            <option value="{{ $service['slug'] }}" @selected($selected === $service['slug'])>{{ $service['name'] }}</option>
                        @endforeach
                    </optgroup>
                    <option value="other" @selected($selected === 'other')>{{ $options['other'] }}</option>
                </select>
                <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-glow-300" />
                </div>
                @error('interest') <p id="enquiry-interest-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="enquiry-message" class="{{ $label }}">How can we help? <span class="text-red-400" aria-hidden="true">*</span></label>
                <textarea id="enquiry-message" name="message" rows="5" required maxlength="3000"
                          placeholder="For example: the type of facility, the equipment you are looking for, and any timing or space details."
                          class="{{ $field }} resize-y" @error('message') aria-invalid="true" aria-describedby="enquiry-message-error" @enderror>{{ old('message') }}</textarea>
                @error('message') <p id="enquiry-message-error" class="mt-1.5 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-ink-3">
                    We use these details only to reply to you. See our <a href="{{ route('privacy') }}" class="underline underline-offset-2 hover:text-ink">Privacy Policy</a>.
                </p>
                <button type="submit" class="group inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-full bg-signal-500 px-7 font-semibold text-navy-950 transition hover:bg-signal-400 active:scale-[0.98] disabled:cursor-wait disabled:opacity-70">
                    <span data-submit-label>{{ $type === 'quote' ? 'Send quote request' : 'Send message' }}</span>
                    <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>
        </form>
    @endif
</div>
