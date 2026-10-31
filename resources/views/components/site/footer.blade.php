@php
    $contact = config('zenray.contact');
    $contactItems = [
        ['icon' => 'mail', 'label' => 'Email', 'value' => $contact['email'], 'href' => $contact['email'] ? 'mailto:'.$contact['email'] : null],
        ['icon' => 'phone', 'label' => 'Phone', 'value' => $contact['phone'], 'href' => $contact['phone'] ? 'tel:'.preg_replace('/[^\d+]/', '', $contact['phone']) : null],
        ['icon' => 'chat', 'label' => 'WhatsApp', 'value' => $contact['whatsapp'] ?? null, 'href' => filled($contact['whatsapp'] ?? null) ? 'https://wa.me/'.preg_replace('/\D/', '', $contact['whatsapp']) : null, 'optional' => true],
        ['icon' => 'map-pin', 'label' => 'Address', 'value' => $contact['address'], 'href' => $contact['map_url'] ?? null],
        ['icon' => 'clock', 'label' => 'Business hours', 'value' => $contact['hours'], 'href' => null],
    ];
    $social = array_filter(config('zenray.social', []));
@endphp

<footer class="relative border-t border-glow-400/15 bg-film-900 text-ink-2" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Footer</h2>

    <div class="mx-auto max-w-7xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-block rounded-lg" aria-label="{{ config('zenray.name') }} home">
                    <img src="{{ asset('images/zenray-logo-light.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" loading="lazy" class="h-10 w-auto">
                </a>
                <p class="mt-6 max-w-sm text-sm leading-relaxed text-navy-200">
                    {{ config('zenray.description') }}
                </p>
                @if ($social)
                    <ul class="mt-6 flex gap-2" aria-label="Social media">
                        @foreach ($social as $network => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-white/5 text-navy-100 ring-1 ring-white/10 transition hover:bg-signal-500 hover:text-navy-950">
                                    <x-social-icon :network="$network" class="size-4" />
                                    <span class="sr-only">{{ ucfirst($network) }} (opens in a new tab)</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-5">
                <div>
                    <h3 class="text-sm font-semibold text-white">Company</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach (config('zenray.navigation') as $item)
                            <li><a href="{{ route($item['route']).(isset($item['fragment']) ? '#'.$item['fragment'] : '') }}" class="text-navy-200 transition-colors hover:text-signal-300">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Equipment</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach (config('zenray.product_categories') as $category)
                            <li><a href="{{ route('products.index') }}#{{ $category['slug'] }}" class="text-navy-200 transition-colors hover:text-signal-300">{{ $category['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Services</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach (config('zenray.services') as $service)
                            <li><a href="{{ route('services.index') }}#{{ $service['slug'] }}" class="text-navy-200 transition-colors hover:text-signal-300">{{ $service['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-semibold text-white">Get in touch</h3>
                <ul class="mt-4 space-y-4 text-sm">
                    @foreach ($contactItems as $item)
                        @continue(($item['optional'] ?? false) && blank($item['value']))
                        <li class="flex gap-3">
                            <x-icon :name="$item['icon']" class="mt-0.5 size-4 shrink-0 text-signal-400" />
                            <span>
                                <span class="sr-only">{{ $item['label'] }}: </span>
                                @if ($item['value'] && $item['href'])
                                    <a href="{{ $item['href'] }}" class="text-navy-100 transition-colors hover:text-signal-300">{{ $item['value'] }}</a>
                                @elseif ($item['value'])
                                    <span class="text-navy-100">{{ $item['value'] }}</span>
                                @else
                                    <span class="text-navy-300 italic">{{ $item['label'] }}: pending configuration</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 text-sm text-navy-300 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('zenray.name') }}. All rights reserved.</p>
            <ul class="flex flex-wrap gap-x-6 gap-y-2">
                <li><a href="{{ route('privacy') }}" class="transition-colors hover:text-signal-300">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="transition-colors hover:text-signal-300">Terms and Conditions</a></li>
                <li><a href="{{ route('credits') }}" class="transition-colors hover:text-signal-300">Image Credits</a></li>
            </ul>
        </div>
    </div>
</footer>
