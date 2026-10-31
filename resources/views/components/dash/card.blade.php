@props(['title' => null, 'description' => null, 'padded' => true])

<section {{ $attributes->class('overflow-hidden rounded-2xl bg-white ring-1 ring-navy-100') }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-navy-100 px-5 py-4 sm:px-6">
            <div>
                @if ($title) <h2 class="font-bold text-navy-900">{{ $title }}</h2> @endif
                @if ($description) <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p> @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif
    <div @class(['p-5 sm:p-6' => $padded])>
        {{ $slot }}
    </div>
</section>
