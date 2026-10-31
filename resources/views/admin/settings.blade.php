<x-layouts.dashboard title="Settings" area="admin">
    <x-slot:subtitle>Changes apply across the whole website as soon as you save.</x-slot:subtitle>

    @php($inputName = fn (string $key) => \App\Http\Controllers\Admin\SettingsController::inputName($key))

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">
            <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-4">
        {{-- Section index --}}
        <nav class="hidden xl:block" aria-label="Settings sections">
            <ul class="sticky top-24 space-y-1">
                @foreach ($groups as $id => $group)
                    <li>
                        <a href="#{{ $id }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-white hover:text-navy-900 hover:shadow-sm">
                            <x-icon :name="$group['icon']" class="size-4 text-signal-600" />
                            {{ $group['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 xl:col-span-3" novalidate>
            @csrf
            @method('PUT')

            @foreach ($groups as $id => $group)
                <section id="{{ $id }}" class="scroll-mt-24 overflow-hidden rounded-2xl bg-white ring-1 ring-navy-100" aria-labelledby="{{ $id }}-title">
                    <header class="flex items-start gap-4 border-b border-navy-100 px-5 py-5 sm:px-6">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-navy-800 text-white"><x-icon :name="$group['icon']" class="size-5" /></span>
                        <div>
                            <h2 id="{{ $id }}-title" class="font-bold text-navy-900">{{ $group['title'] }}</h2>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $group['description'] }}</p>
                        </div>
                    </header>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        @foreach ($group['fields'] as $key => $field)
                            <x-form.input :name="$inputName($key)" :label="$field['label']" :type="$field['type']"
                                          :value="\App\Support\SiteSettings::value($key)" :help="$field['help'] ?? null"
                                          :required="in_array('required', $field['rules'], true)"
                                          :class="$field['type'] === 'textarea' ? 'sm:col-span-2' : ''" />
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="sticky bottom-4 z-10 flex items-center justify-between gap-4 rounded-2xl bg-navy-900 p-4 shadow-2xl sm:px-6">
                <p class="text-sm text-navy-200">Saved values replace the defaults everywhere on the site.</p>
                <button type="submit" class="btn-shine inline-flex h-11 shrink-0 items-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">Save settings</button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
