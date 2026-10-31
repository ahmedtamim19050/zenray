<x-layouts.auth title="Sign in" heading="Welcome back" subheading="Sign in to view your enquiries and quote requests.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf
        <x-form.input name="email" label="Email" type="email" required autocomplete="email" autofocus />
        <div>
            <x-form.input name="password" label="Password" type="password" required autocomplete="current-password" />
            <div class="mt-3 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-navy-300 text-navy-800 focus:ring-navy-700">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-navy-800 underline-offset-4 hover:underline">Forgot password?</a>
            </div>
        </div>
        <button type="submit" class="btn-shine inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-navy-800 font-semibold text-white transition hover:bg-navy-700">
            Sign in <x-icon name="arrow-right" class="size-4" />
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600">
        New to {{ config('zenray.short_name') }}?
        <a href="{{ route('register') }}" class="font-semibold text-navy-800 underline-offset-4 hover:underline">Create an account</a>
    </p>
</x-layouts.auth>
