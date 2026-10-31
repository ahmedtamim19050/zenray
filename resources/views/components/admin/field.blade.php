{{--
    One admin form field, rendered from a definition in App\Support\AdminContent
    (or Admin\PostController::fields()). Light dashboard styling.
    <x-admin.field name="title" :field="$field" :model="$item" />
--}}
@props(['name', 'field', 'model'])

@php
    $type = $field['type'];
    $id = 'field-'.$name;
    $error = $errors->first($name) ?: $errors->first("{$name}_library");
    $help = $field['help'] ?? null;
    $required = in_array('required', $field['rules'] ?? [], true) || ($field['required'] ?? false);
    $describedBy = trim(($help ? "{$id}-help " : '').($error ? "{$id}-error" : ''));

    $stored = $model->exists ? $model->{$name} : ($field['default'] ?? null);
    $value = match ($type) {
        'lines' => old($name, implode("\n", (array) ($stored ?? []))),
        'date' => old($name, $stored instanceof \DateTimeInterface ? $stored->format('Y-m-d') : $stored),
        'checkbox' => (bool) old($name, $stored),
        default => old($name, $stored),
    };
    $options = isset($field['options']) ? value($field['options']) : [];

    $input = 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-base text-navy-900 shadow-sm ring-1 ring-navy-200 placeholder:text-slate-400 transition focus:ring-2 focus:ring-navy-700 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600 sm:text-[0.9375rem]';
    $aria = ($describedBy ? 'aria-describedby="'.$describedBy.'"' : '').($error ? ' aria-invalid="true"' : '');
@endphp

<div {{ $attributes }}>
    @if ($type === 'checkbox')
        <input type="hidden" name="{{ $name }}" value="0">
        <label for="{{ $id }}" class="flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-navy-100 transition hover:ring-navy-200">
            <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value) {!! $aria !!}
                   class="mt-0.5 size-5 shrink-0 rounded border-navy-300 text-navy-800 accent-navy-800 focus:ring-navy-700">
            <span>
                <span class="block text-sm font-semibold text-navy-800">{{ $field['label'] }}</span>
                @if ($help) <span id="{{ $id }}-help" class="mt-0.5 block text-sm text-slate-500">{{ $help }}</span> @endif
            </span>
        </label>
    @else
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-navy-800">
            {{ $field['label'] }}
            @if ($required) <span class="text-red-700" aria-hidden="true">*</span> @endif
        </label>

        @switch($type)
            @case('textarea')
            @case('lines')
                <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $type === 'lines' ? 4 : 3 }}" @required($required) {!! $aria !!} class="{{ $input }} resize-y">{{ $value }}</textarea>
                @break

            @case('markdown')
                <div class="overflow-hidden rounded-xl ring-1 ring-navy-200 focus-within:ring-2 focus-within:ring-navy-700" data-markdown-editor>
                    <div class="flex flex-wrap items-center gap-1 border-b border-navy-100 bg-slate-50 px-2 py-1.5" role="toolbar" aria-label="Formatting">
                        @foreach ([['## ', '', 'Heading', 'H2'], ['**', '**', 'Bold', 'B'], ['_', '_', 'Italic', 'I'], ['- ', '', 'Bullet list', '•'], ['1. ', '', 'Numbered list', '1.'], ['> ', '', 'Quote', '❝'], ['[', '](https://)', 'Link', 'Link']] as [$before, $after, $title, $text])
                            <button type="button" data-md-before="{{ $before }}" data-md-after="{{ $after }}" title="{{ $title }}"
                                    class="min-w-8 rounded-lg px-2 py-1 font-mono text-sm font-semibold text-navy-700 hover:bg-white hover:shadow-sm">
                                {{ $text }}<span class="sr-only"> ({{ $title }})</span>
                            </button>
                        @endforeach
                        <span class="ml-auto pr-1 text-xs text-slate-500">Markdown supported</span>
                    </div>
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="18" @required($required) {!! $aria !!}
                              class="block w-full resize-y border-0 bg-white px-4 py-3 font-mono text-sm leading-relaxed text-navy-900 focus:ring-0 focus:outline-none">{{ $value }}</textarea>
                </div>
                @break

            @case('select')
                <div class="relative">
                    <select id="{{ $id }}" name="{{ $name }}" @required($required) {!! $aria !!} class="{{ $input }} appearance-none pr-11">
                        @unless ($required) <option value="">None</option> @else <option value="" disabled @selected(blank($value))>Choose…</option> @endunless
                        @foreach ($options as $key => $label)
                            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-navy-700" />
                </div>
                @break

            @case('icon')
                <div class="grid grid-cols-5 gap-2 sm:grid-cols-10" role="radiogroup" aria-labelledby="{{ $id }}-legend">
                    <span id="{{ $id }}-legend" class="sr-only">{{ $field['label'] }}</span>
                    @foreach (\App\Support\AdminContent::ICONS as $icon)
                        <label class="relative flex aspect-square cursor-pointer items-center justify-center rounded-xl bg-white text-navy-700 ring-1 ring-navy-200 transition hover:ring-navy-400 has-checked:bg-navy-800 has-checked:text-white has-checked:ring-navy-800" title="{{ $icon }}">
                            <input type="radio" name="{{ $name }}" value="{{ $icon }}" @checked($value === $icon) @if ($loop->first) id="{{ $id }}" @endif class="sr-only">
                            <x-icon :name="$icon" class="size-5" />
                            <span class="sr-only">{{ $icon }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('image')
                <div class="@container rounded-xl bg-slate-50 p-4 ring-1 ring-navy-100" data-image-field>
                <div class="flex flex-col gap-4 @lg:flex-row">
                    <div class="relative aspect-[4/3] w-full shrink-0 overflow-hidden rounded-lg bg-white ring-1 ring-navy-100 @lg:w-48">
                        @if ($model->{$name})
                            <x-photo :name="$model->{$name}" alt="Current image" sizes="200px" data-image-preview />
                        @else
                            <img src="" alt="" class="hidden size-full object-cover" data-image-preview>
                            <span class="absolute inset-0 flex items-center justify-center text-sm text-slate-400" data-image-empty>No image</span>
                        @endif
                    </div>
                    <div class="grid flex-1 content-start gap-3">
                        <div>
                            <span class="mb-1 block text-xs font-semibold tracking-wide text-slate-500 uppercase">Upload a new image</span>
                            <input type="file" id="{{ $id }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" {!! $aria !!}
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-navy-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-700">
                            <span class="mt-1 block text-xs text-slate-500">JPG, PNG or WebP, up to 5 MB. Landscape photos work best.</span>
                        </div>
                        <div>
                            <label for="{{ $id }}-library" class="mb-1 block text-xs font-semibold tracking-wide text-slate-500 uppercase">Or choose from the photo library</label>
                            <div class="relative">
                                <select id="{{ $id }}-library" name="{{ $name }}_library" class="{{ $input }} appearance-none py-2 pr-11" data-image-library>
                                    <option value="">{{ $model->{$name} ? 'Keep current image' : 'Choose a photo…' }}</option>
                                    @foreach (\App\Support\Photo::library() as $key => $label)
                                        <option value="{{ $key }}" data-src="{{ \App\Support\Photo::url($key) }}" @selected(old("{$name}_library") === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-navy-700" />
                            </div>
                        </div>
                        @if ($model->{$name} && ! $required)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="{{ $name }}_remove" value="1" class="size-4 rounded accent-red-700"> Remove image
                            </label>
                        @endif
                    </div>
                </div>
                </div>
                @break

            @default
                <input type="{{ match ($type) { 'number' => 'number', 'date' => 'date', default => 'text' } }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
                       @required($required) {!! $aria !!} @if ($type === 'number') min="0" step="1" inputmode="numeric" @endif
                       @if ($type === 'slug') placeholder="created-automatically" pattern="[A-Za-z0-9_\-]*" @endif
                       class="{{ $input }} {{ $type === 'slug' ? 'font-mono text-sm' : '' }}">
        @endswitch

        @if ($help && $type !== 'checkbox')
            <p id="{{ $id }}-help" class="mt-1.5 text-sm text-slate-500">{{ $help }}</p>
        @endif
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700">{{ $error }}</p>
    @endif
</div>
