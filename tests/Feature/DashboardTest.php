<?php

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function makeEnquiry(array $attributes = []): Enquiry
{
    return Enquiry::create(array_merge([
        'type' => 'quote',
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'interest' => 'digital-radiography',
        'message' => 'We would like a quote for a detector.',
    ], $attributes));
}

test('customer dashboard pages render', function (string $uri) {
    $this->actingAs(User::factory()->create())->get($uri)->assertOk();
})->with(['/dashboard', '/dashboard/enquiries', '/dashboard/enquiries/new', '/account/profile']);

test('an admin visiting the customer dashboard is sent to the admin dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});

test('enquiries sent while signed in are linked to the customer and listed', function () {
    Mail::fake();
    $customer = User::factory()->create();

    $this->actingAs($customer)->post('/enquiries', [
        'name' => $customer->name, 'email' => $customer->email,
        'interest' => 'installation', 'message' => 'Please help install our new room.', 'type' => 'quote',
    ])->assertSessionHas('enquiry_status', 'sent');

    $enquiry = Enquiry::first();
    expect($enquiry->user_id)->toBe($customer->id);

    $this->get('/dashboard/enquiries')->assertSee('Equipment Installation');
    $this->get('/dashboard/enquiries/'.$enquiry->id)->assertOk()->assertSee('Please help install our new room.');
});

test('customers cannot view other customers’ enquiries', function () {
    $other = makeEnquiry(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())->get('/dashboard/enquiries/'.$other->id)->assertNotFound();
});

test('customers can update their profile and password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/account/profile', [
        'name' => 'New Name', 'email' => 'new@example.com', 'organization' => 'Clinic', 'phone' => '+1 555 0100',
    ])->assertSessionHas('status');
    expect($user->fresh()->name)->toBe('New Name');

    $this->put('/account/password', ['current_password' => 'wrong', 'password' => 'another-pass-9', 'password_confirmation' => 'another-pass-9'])
        ->assertSessionHasErrorsIn('password', 'current_password');

    $this->put('/account/password', ['current_password' => 'password', 'password' => 'another-pass-9', 'password_confirmation' => 'another-pass-9'])
        ->assertSessionHas('status');
});

test('admin pages render, including with data', function (string $uri) {
    $customer = User::factory()->create();
    makeEnquiry(['user_id' => $customer->id]);
    makeEnquiry(['status' => 'closed', 'interest' => 'other']);

    $this->actingAs(User::factory()->admin()->create())->get($uri)->assertOk();
})->with(['/admin', '/admin/enquiries', '/admin/enquiries?status=new&q=Jane', '/admin/enquiries/1', '/admin/customers', '/admin/customers/1', '/admin/settings', '/account/profile']);

test('the admin dashboard shows real counts', function () {
    makeEnquiry();
    makeEnquiry();
    makeEnquiry(['status' => 'closed']);

    $this->actingAs(User::factory()->admin()->create())->get('/admin')
        ->assertViewHas('stats', fn ($stats) => $stats['new'] === 2 && $stats['open'] === 2 && $stats['enquiries'] === 3);
});

test('admins can update an enquiry status and the customer sees it', function () {
    $customer = User::factory()->create();
    $enquiry = makeEnquiry(['user_id' => $customer->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch('/admin/enquiries/'.$enquiry->id, ['status' => 'quoted', 'admin_note' => 'Sent PDF quote.'])
        ->assertSessionHas('status');

    expect($enquiry->fresh()->status)->toBe('quoted');

    $this->actingAs($customer)->get('/dashboard/enquiries/'.$enquiry->id)->assertSee('Quote sent')->assertDontSee('Sent PDF quote.');
});

test('invalid statuses are rejected', function () {
    $enquiry = makeEnquiry();

    $this->actingAs(User::factory()->admin()->create())
        ->patch('/admin/enquiries/'.$enquiry->id, ['status' => 'deleted'])
        ->assertSessionHasErrors('status');
});

test('admin accounts are not shown as customers', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/customers/'.$admin->id)->assertNotFound();
});
