<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('auth pages render for guests', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/login', '/register', '/forgot-password']);

test('a visitor can register as a customer', function () {
    $this->post('/register', [
        'name' => 'Jane Doe',
        'organization' => 'City Clinic',
        'email' => 'jane@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'jane@example.com')->first();
    expect($user->role)->toBe(User::ROLE_CUSTOMER);
    $this->assertAuthenticatedAs($user);
});

test('registration cannot grant the admin role', function () {
    $this->post('/register', [
        'name' => 'Sneaky', 'email' => 'sneaky@example.com', 'role' => 'admin',
        'password' => 'secret-pass-123', 'password_confirmation' => 'secret-pass-123',
    ]);

    expect(User::where('email', 'sneaky@example.com')->first()->isAdmin())->toBeFalse();
});

test('customers and admins land on their own dashboards after signing in', function (bool $admin, string $route) {
    $user = $admin ? User::factory()->admin()->create() : User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route($route));
})->with([[false, 'dashboard'], [true, 'admin.dashboard']]);

test('wrong passwords are rejected', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'nope'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('users can sign out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('home'));
    $this->assertGuest();
});

test('guests are sent to sign in from private pages', function (string $uri) {
    $this->get($uri)->assertRedirect(route('login'));
})->with(['/dashboard', '/admin', '/admin/settings', '/account/profile']);

test('customers cannot open admin pages', function (string $uri) {
    $this->actingAs(User::factory()->create())->get($uri)->assertForbidden();
})->with(['/admin', '/admin/enquiries', '/admin/customers', '/admin/settings']);

test('a password reset link can be requested and used', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))->assertOk();

        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ])->assertRedirect(route('login'));

        return true;
    });

    $this->post('/login', ['email' => $user->email, 'password' => 'brand-new-pass-1'])->assertRedirect(route('dashboard'));
});

test('the create-admin command creates an admin account', function () {
    $this->artisan('zenray:create-admin', ['email' => 'boss@zenray.test', '--name' => 'Boss', '--password' => 'super-secret-1'])
        ->assertSuccessful();

    expect(User::where('email', 'boss@zenray.test')->first()->isAdmin())->toBeTrue();
});
