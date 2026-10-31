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
