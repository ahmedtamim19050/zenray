<x-layouts.auth title="Create an account" heading="Create your account" subheading="Keep track of your quote requests and enquiries with our team.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf
        <x-form.input name="name" label="Your name" required autocomplete="name" autofocus />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="organization" label="Organization or facility" autocomplete="organization" />
            <x-form.input name="phone" label="Phone" type="tel" autocomplete="tel" />
        </div>
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="password" label="Password" type="password" required autocomplete="new-password" help="At least 8 characters." />
            <x-form.input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
        </div>
        <p class="text-sm text-slate-500">
            By creating an account you agree to our <a href="{{ route('terms') }}" class="underline underline-offset-2">Terms</a>
            and <a href="{{ route('privacy') }}" class="underline underline-offset-2">Privacy Policy</a>.
        </p>
        <button type="submit" class="btn-shine inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-signal-500 font-semibold text-navy-950 transition hover:bg-signal-400">
            Create account <x-icon name="arrow-right" class="size-4" />
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-navy-800 underline-offset-4 hover:underline">Sign in</a>
    </p>
</x-layouts.auth>
