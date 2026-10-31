<x-layouts.auth title="Forgot password" heading="Reset your password" subheading="Enter your email and we will send you a link to choose a new password.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" autofocus />
        <button type="submit" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-navy-800 font-semibold text-white transition hover:bg-navy-700">
            Email reset link
        </button>
    </form>
    <p class="mt-8 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-navy-800 underline-offset-4 hover:underline">Back to sign in</a>
    </p>
</x-layouts.auth>
