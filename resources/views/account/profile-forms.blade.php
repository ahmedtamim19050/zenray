{{-- Profile + password forms, shared by the customer and admin profile pages. --}}
<div class="grid gap-6 xl:grid-cols-2">
    <x-dash.card title="Profile" description="Your name and contact details.">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5" novalidate>
            @csrf
            @method('PATCH')
            <x-form.input name="name" label="Full name" :value="$user->name" required autocomplete="name" />
            <x-form.input name="organization" label="Organization or facility" :value="$user->organization" autocomplete="organization" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
                <x-form.input name="phone" label="Phone" type="tel" :value="$user->phone" autocomplete="tel" />
            </div>
            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-navy-800 px-6 text-sm font-semibold text-white transition hover:bg-navy-700">Save profile</button>
        </form>
    </x-dash.card>

    <x-dash.card title="Password" description="Use at least 8 characters.">
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')
            <x-form.input name="current_password" label="Current password" type="password" bag="password" required autocomplete="current-password" />
            <x-form.input name="password" label="New password" type="password" bag="password" required autocomplete="new-password" />
            <x-form.input name="password_confirmation" label="Confirm new password" type="password" bag="password" required autocomplete="new-password" />
            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-navy-800 px-6 text-sm font-semibold text-white transition hover:bg-navy-700">Change password</button>
        </form>
    </x-dash.card>
</div>
