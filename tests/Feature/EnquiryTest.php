<?php

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['zenray.enquiries_to' => 'team@zenray.test']);
});

function validEnquiry(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'organization' => 'City Clinic',
        'email' => 'jane@example.com',
        'phone' => '',
        'interest' => 'digital-radiography',
        'message' => 'We would like to upgrade our X-ray room to digital.',
        'type' => 'quote',
    ], $overrides);
}

test('homepage, quote and contact pages render', function (string $uri) {
    $this->get($uri)->assertOk()->assertSee('name="interest"', false);
})->with(['/', '/request-a-quote', '/contact']);

test('a valid enquiry is emailed to the team', function () {
    $this->from('/request-a-quote')
        ->post('/enquiries', validEnquiry())
        ->assertRedirect('/request-a-quote#enquiry')
        ->assertSessionHas('enquiry_status', 'sent');

    Mail::assertSent(EnquiryReceived::class, function (EnquiryReceived $mail) {
        return $mail->hasTo('team@zenray.test')
            && $mail->hasReplyTo('jane@example.com')
            && $mail->enquiry['interest_label'] === 'Digital Radiography'
            && ! array_key_exists('website', $mail->enquiry);
    });
});

test('the email renders even when optional fields are missing', function () {
    $mail = new EnquiryReceived([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'interest_label' => 'Equipment Installation',
        'message' => 'Hello there, please call me.',
    ]);

    $mail->assertSeeInText('Phone:         -');
    $mail->assertSeeInText('Equipment Installation');
});

test('a phone number can be given instead of an email', function () {
    $this->post('/enquiries', validEnquiry(['email' => '', 'phone' => '+1 (555) 010-2030']))
        ->assertSessionHasNoErrors();

    Mail::assertSent(EnquiryReceived::class);
});

test('required fields are validated and the visitor returns to the form', function () {
    $this->from('/contact')
        ->post('/enquiries', ['type' => 'quote'])
        ->assertRedirect('/contact#enquiry')
        ->assertSessionHasErrors(['name', 'email', 'phone', 'interest', 'message']);

    Mail::assertNothingSent();
});

test('an unknown interest is rejected', function () {
    $this->post('/enquiries', validEnquiry(['interest' => 'spaceships']))
        ->assertSessionHasErrors('interest');
});

test('submissions that fill the honeypot are rejected', function () {
    $this->post('/enquiries', validEnquiry(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    Mail::assertNothingSent();
});

test('the interest can be preselected from the query string', function () {
    $this->get('/request-a-quote?interest=installation')
        ->assertOk()
        ->assertSee('<option value="installation" selected', false);
});

test('a mail failure still keeps the enquiry for the admin dashboard', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->from('/contact')
        ->post('/enquiries', validEnquiry())
        ->assertRedirect('/contact#enquiry')
        ->assertSessionHas('enquiry_status', 'sent');

    expect(Enquiry::where('email', 'jane@example.com')->exists())->toBeTrue();
});

test('enquiries are saved with status new and no account when sent as a guest', function () {
    $this->post('/enquiries', validEnquiry());

    $enquiry = Enquiry::first();
    expect($enquiry->status)->toBe('new')
        ->and($enquiry->user_id)->toBeNull()
        ->and($enquiry->type)->toBe('quote');
});
