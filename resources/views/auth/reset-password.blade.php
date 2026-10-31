<x-layouts.auth title="Choose a new password" heading="Choose a new password">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-form.input name="email" label="Email" type="email" :value="$email" required autocomplete="email" />
        <x-form.input name="password" label="New password" type="password" required autocomplete="new-password" help="At least 8 characters." autofocus />
        <x-form.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
        <button type="submit" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-navy-800 font-semibold text-white transition hover:bg-navy-700">
            Save new password
        </button>
    </form>
</x-layouts.auth>
