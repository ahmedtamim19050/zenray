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

<footer class="relative border-t border-film-600 bg-film-900 text-ink-2" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Footer</h2>

    <div class="mx-auto max-w-7xl px-4 pt-16 pb-8 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-block rounded-lg" aria-label="{{ config('zenray.name') }} home">
                    <x-site.logo loading="lazy" class="h-9 w-auto" />
                </a>
                <p class="mt-6 max-w-sm text-sm leading-relaxed text-ink-2">
                    {{ config('zenray.description') }}
                </p>
                @if ($social)
                    <ul class="mt-6 flex gap-2" aria-label="Social media">
                        @foreach ($social as $network => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="flex size-11 items-center justify-center rounded-full bg-film-700 text-ink-2 ring-1 ring-film-600 transition hover:bg-signal-500 hover:text-navy-950">
                                    <x-social-icon :network="$network" class="size-4" />
                                    <span class="sr-only">{{ ucfirst($network) }} (opens in a new tab)</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:col-span-5">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Company</h3>
                    <ul class="mt-4 space-y-4 text-sm">
                        @foreach (config('zenray.navigation') as $item)
                            <li><a href="{{ route($item['route']).(isset($item['fragment']) ? '#'.$item['fragment'] : '') }}" class="text-ink-2 transition-colors hover:text-signal-text">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Equipment</h3>
                    <ul class="mt-4 space-y-4 text-sm">
                        @foreach (config('zenray.product_categories') as $category)
                            <li><a href="{{ route('products.index') }}#{{ $category['slug'] }}" class="text-ink-2 transition-colors hover:text-signal-text">{{ $category['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Services</h3>
                    <ul class="mt-4 space-y-4 text-sm">
                        @foreach (config('zenray.services') as $service)
                            <li><a href="{{ route('services.index') }}#{{ $service['slug'] }}" class="text-ink-2 transition-colors hover:text-signal-text">{{ $service['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-semibold text-ink">Get in touch</h3>
                <ul class="mt-4 space-y-4 text-sm">
                    @foreach ($contactItems as $item)
                        @continue(($item['optional'] ?? false) && blank($item['value']))
                        <li class="flex gap-3">
                            <x-icon :name="$item['icon']" class="mt-0.5 size-4 shrink-0 text-signal-text" />
                            <span>
                                <span class="sr-only">{{ $item['label'] }}: </span>
                                @if ($item['value'] && $item['href'])
                                    <a href="{{ $item['href'] }}" class="text-ink-2 transition-colors hover:text-signal-text">{{ $item['value'] }}</a>
                                @elseif ($item['value'])
                                    <span class="text-ink-2">{{ $item['value'] }}</span>
                                @else
                                    <span class="text-ink-3 italic">{{ $item['label'] }}: pending configuration</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-film-600 pt-8 text-sm text-ink-3 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('zenray.name') }}. All rights reserved.</p>
            <ul class="flex flex-wrap gap-x-6 gap-y-2">
                <li><a href="{{ route('privacy') }}" class="transition-colors hover:text-signal-text">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="transition-colors hover:text-signal-text">Terms and Conditions</a></li>
                <li><a href="{{ route('credits') }}" class="transition-colors hover:text-signal-text">Image Credits</a></li>
            </ul>
        </div>
    </div>
</footer>
