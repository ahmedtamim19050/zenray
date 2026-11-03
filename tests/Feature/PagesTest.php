<?php

use App\Models\User;

test('public pages render', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/', '/products', '/services', '/about', '/portfolio', '/contact', '/request-a-quote', '/privacy-policy', '/terms-and-conditions', '/image-credits']);

test('the header links to the account area when signed in', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertSee('My account')->assertSee(route('dashboard'), false);
});

test('legal pages show the template notice to admins only', function () {
    $this->get('/privacy-policy')->assertDontSee('Visible to admins only');
    $this->actingAs(User::factory()->admin()->create())->get('/privacy-policy')->assertSee('Visible to admins only');
});

test('the public site is light by default and offers a dark mode switch', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee('<meta name="theme-color" content="#FFFFFF">', false)
        ->assertDontSee('<html data-theme', false);
});

test('product availability stays available in the quick view after moving off the compact card', function () {
    $this->get('/')->assertOk()->assertSee('data-availability="Availability and pricing on request."', false);
});

test('the auto-advancing hero slideshow offers a pause control', function () {
    $this->get('/')->assertOk()->assertSee('data-carousel-toggle', false)->assertSee('Pause slideshow');
});

test('the contact section shows only the details that are set up', function () {
    config(['zenray.contact.phone' => '+880 1711 000000', 'zenray.contact.email' => null, 'zenray.contact.address' => null, 'zenray.contact.hours' => null]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Call us')
        ->assertSee('tel:+8801711000000', false)
        ->assertDontSee('Email us')
        ->assertDontSee('Pending configuration');
});

test('the dashboard and sign-in pages do not read the saved theme', function () {
    $this->get('/login')->assertOk()->assertDontSee('zenray-theme');
    $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertDontSee('zenray-theme');
});
