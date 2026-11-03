@php
    $editing = $item->exists;
    $wide = ['textarea', 'markdown', 'image', 'icon', 'lines', 'checkbox'];
@endphp

<x-layouts.dashboard :title="($editing ? 'Edit ' : 'Add ').Str::lower($config['singular'])" area="admin">
    <x-slot:subtitle>
        <a href="{{ route('admin.content.index', $resource) }}" class="font-semibold text-navy-800 hover:text-signal-700">← {{ $config['plural'] }}</a>
    </x-slot:subtitle>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">
            <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('admin.content.update', [$resource, $item->id]) : route('admin.content.store', $resource) }}"
          class="max-w-4xl">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-dash.card>
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($config['fields'] as $name => $field)
                    <x-admin.field :name="$name" :field="$field" :model="$item" @class(['sm:col-span-2' => in_array($field['type'], $wide, true)]) />
                @endforeach
            </div>
        </x-dash.card>

        <div class="sticky bottom-4 z-10 mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-navy-900 p-4 shadow-2xl sm:px-6">
            <a href="{{ route('admin.content.index', $resource) }}" class="text-sm font-semibold text-navy-200 hover:text-white">Cancel</a>
            <button type="submit" class="inline-flex h-11 shrink-0 items-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                {{ $editing ? 'Save changes' : 'Add '.Str::lower($config['singular']) }}
            </button>
        </div>
    </form>
</x-layouts.dashboard>
