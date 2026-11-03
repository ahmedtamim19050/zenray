{{-- Brand logo in the right colours for the current theme. Attributes apply to both images; size it with h-*. --}}
<img src="{{ asset('images/zenray-logo.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" {{ $attributes->class('dark:hidden') }}>
<img src="{{ asset('images/zenray-logo-light.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" {{ $attributes->class('hidden dark:block') }}>
