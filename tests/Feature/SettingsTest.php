<?php

use App\Mail\EnquiryReceived;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Mail;

function saveSettings(array $overrides = [])
{
    return test()->actingAs(User::factory()->admin()->create())->put('/admin/settings', array_merge([
        'name' => 'ZenRay Medical Systems',
        'contact__email' => 'hello@zenray.test',
        'contact__phone' => '+1 (555) 010-2030',
        'contact__address' => '1 Imaging Way, Springfield',
        'contact__hours' => 'Mon–Fri, 9:00–17:00',
        'social__linkedin' => 'https://www.linkedin.com/company/zenray',
    ], $overrides));
}

test('admins can save contact information', function () {
    saveSettings()->assertSessionHasNoErrors()->assertSessionHas('status');

    expect(Setting::where('key', 'contact.email')->value('value'))->toBe('hello@zenray.test');
});

test('saved settings appear across the website', function () {
    saveSettings();
    SiteSettings::apply();

    $this->get('/')->assertSee('hello@zenray.test')->assertSee('+1 (555) 010-2030')->assertSee('1 Imaging Way, Springfield')
        ->assertSee('https://www.linkedin.com/company/zenray', false);
    $this->get('/contact')->assertSee('Mon–Fri, 9:00–17:00');
});

test('settings are validated', function () {
    saveSettings(['contact__email' => 'not-an-email', 'name' => '', 'social__facebook' => 'nope'])
        ->assertSessionHasErrors(['contact__email', 'name', 'social__facebook']);
});

test('clearing a field hides it again', function () {
    saveSettings();
    saveSettings(['contact__phone' => '']);

    expect(Setting::where('key', 'contact.phone')->value('value'))->toBeNull();
});

test('enquiries are emailed to the configured address', function () {
    Mail::fake();
    saveSettings(['enquiries_to' => 'sales@zenray.test']);
    SiteSettings::apply();

    $this->post('/enquiries', ['name' => 'Jane', 'email' => 'jane@example.com', 'interest' => 'other', 'message' => 'Hello there, a question.']);

    Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->hasTo('sales@zenray.test'));
});

test('customers cannot change settings', function () {
    $this->actingAs(User::factory()->create())->put('/admin/settings', ['name' => 'Hacked'])->assertForbidden();

    expect(Setting::count())->toBe(0);
});
