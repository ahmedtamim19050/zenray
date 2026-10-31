{{--
    Labelled form field with error + help text.
    <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
    Pass type="textarea" for a multi-line field. Use :input-name when the
    submitted name differs from the error key; bag selects an error bag.
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'help' => null,
    'required' => false,
    'inputName' => null,
    'bag' => 'default',
    'rows' => 3,
])

@php
    $inputName ??= $name;
    $id = 'field-'.str_replace(['.', '_', '[', ']'], '-', $inputName);
    $error = $errors->getBag($bag)->first($inputName);
    $describedBy = trim(($help ? "{$id}-help " : '').($error ? "{$id}-error" : ''));
    $classes = 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-base text-navy-900 shadow-sm ring-1 ring-navy-200 placeholder:text-slate-400 transition focus:ring-2 focus:ring-navy-700 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600 sm:text-[0.9375rem]';
    $current = $type === 'password' ? null : old($inputName, $value);
@endphp

<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-navy-800">
        {{ $label }}
        @if ($required) <span class="text-red-700" aria-hidden="true">*</span> @endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $inputName }}" rows="{{ $rows }}" @required($required)
                  @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif
                  {{ $attributes->except('class')->merge(['class' => $classes.' resize-y']) }}>{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $inputName }}" type="{{ $type }}" value="{{ $current }}" @required($required)
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif
               {{ $attributes->except('class')->merge(['class' => $classes]) }}>
    @endif

    @if ($help)
        <p id="{{ $id }}-help" class="mt-1.5 text-sm text-slate-500">{{ $help }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700">{{ $error }}</p>
    @endif
</div>
