<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Profile + password for any signed-in user (customers and admins).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view($request->user()->isAdmin() ? 'admin.profile' : 'customer.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+()\-.]{6,40}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:180', Rule::unique('users')->ignore($user->id)],
        ]));

        return back()->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'Your password has been changed.');
    }
}
