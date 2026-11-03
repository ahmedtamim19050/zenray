{{--
    Field whose label sits inside it and glides up on focus or once it has a value.
    <x-form.floating name="email" label="Email" type="email" :value="$user->email" />
    Pass type="textarea" for a multi-line field and describedby for an id of help text.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'describedby' => null, 'rows' => 4])

@php
    $id = "enquiry-{$name}";
    $error = $errors->first($name);
    $describedBy = trim($describedby.' '.($error ? "{$id}-error" : ''));
    $classes = 'peer block w-full rounded-xl border-0 bg-film-900 px-4 pt-7 pb-2.5 text-base text-ink ring-1 ring-film-500 transition duration-200 hover:ring-ink-3 focus:bg-film-950 focus:ring-2 focus:ring-signal-500 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600';
@endphp

<div {{ $attributes->only('class') }}>
    <div class="relative">
        @if ($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder=" " @required($required)
                      @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif
                      {{ $attributes->except('class')->merge(['class' => $classes.' resize-y']) }}>{{ old($name, $value) }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder=" " @required($required)
                   @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif
                   {{ $attributes->except('class')->merge(['class' => $classes]) }}>
        @endif
        <label for="{{ $id }}" class="pointer-events-none absolute top-2.5 left-4 text-xs font-medium text-ink-2 transition-all duration-200 ease-calm peer-placeholder-shown:top-4.5 peer-placeholder-shown:text-base peer-focus:top-2.5 peer-focus:text-xs peer-focus:text-signal-text motion-reduce:transition-none">
            {{ $label }}@if ($required) <span class="text-red-700 dark:text-red-300" aria-hidden="true">*</span>@endif
        </label>
    </div>
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
    @endif
</div>
