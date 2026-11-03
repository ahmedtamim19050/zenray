{{-- Light / dark switch. Behaviour lives in app.js ([data-theme-toggle]). --}}
<button type="button" data-theme-toggle aria-pressed="false" title="Switch to dark mode"
        class="inline-flex size-11 items-center justify-center rounded-full text-ink-2 transition-colors hover:bg-film-700 hover:text-ink">
    <x-icon name="moon" class="size-5 dark:hidden" />
    <x-icon name="sun" class="hidden size-5 dark:block" />
    <span class="sr-only">Dark mode</span>
</button>
