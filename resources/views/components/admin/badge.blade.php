{{-- Status pill. Pass a [label, classes] pair, e.g. :state="$invoice->state()". --}}
@props(['state'])

<span {{ $attributes->class('inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap ring-1 ring-inset '.$state[1]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $state[0] }}
</span>
