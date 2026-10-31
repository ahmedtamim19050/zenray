@props(['icon' => 'folder', 'title', 'text' => null])

<div {{ $attributes->class('flex flex-col items-center px-6 py-14 text-center') }}>
    <span class="flex size-14 items-center justify-center rounded-2xl bg-slate-50 text-navy-600 ring-1 ring-navy-100">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-4 font-bold text-navy-900">{{ $title }}</p>
    @if ($text) <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p> @endif
    @if ($slot->isNotEmpty()) <div class="mt-6">{{ $slot }}</div> @endif
</div>
