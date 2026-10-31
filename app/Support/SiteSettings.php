<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Throwable;

/**
 * Registry of admin-editable site settings.
 *
 * Each field key is a path under config('zenray'), so a saved value
 * transparently overrides the default everywhere the site reads it.
 */
class SiteSettings
{
    /**
     * Groups of fields shown on the admin settings page.
     *
     * @return array<string, array{title: string, description: string, icon: string, fields: array<string, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            'company' => [
                'title' => 'Company',
                'description' => 'Your business name and the short description used across the site and in search results.',
                'icon' => 'layers',
                'fields' => [
                    'name' => ['label' => 'Company name', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                    'tagline' => ['label' => 'Tagline', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:160']],
                    'description' => ['label' => 'Short description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300'], 'help' => 'Used in the footer and as the default search-engine description.'],
                ],
            ],
            'contact' => [
                'title' => 'Contact information',
                'description' => 'Shown in the header, footer, contact page and quote page. Leave a field empty to hide it.',
                'icon' => 'phone',
                'fields' => [
                    'contact.email' => ['label' => 'Email address', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:180']],
                    'contact.phone' => ['label' => 'Phone number', 'type' => 'tel', 'rules' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+()\-.]{6,40}$/']],
                    'contact.whatsapp' => ['label' => 'WhatsApp number', 'type' => 'tel', 'rules' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+()\-.]{6,40}$/'], 'help' => 'Include the country code, e.g. +1 555 010 2030.'],
                    'contact.address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300']],
                    'contact.hours' => ['label' => 'Business hours', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120'], 'help' => 'For example: Mon–Fri, 9:00–17:00'],
                    'contact.map_url' => ['label' => 'Map link', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500'], 'help' => 'A Google Maps (or similar) link to your location.'],
                ],
            ],
            'enquiries' => [
                'title' => 'Enquiries',
                'description' => 'Where quote requests and messages from the website are emailed.',
                'icon' => 'mail',
                'fields' => [
                    'enquiries_to' => ['label' => 'Send enquiries to', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:180'], 'help' => 'If empty, the contact email above is used.'],
                ],
            ],
            'billing' => [
                'title' => 'Billing, quotes & invoices',
                'description' => 'Defaults for new quotations and invoices. Changes apply to documents created after saving.',
                'icon' => 'document',
                'fields' => [
                    'billing.currency' => ['label' => 'Currency code', 'type' => 'text',  'rules' => ['nullable', 'string', 'size:3', 'alpha'], 'help' => 'Three letters, e.g. USD, EUR, GBP, BDT.'],
                    'billing.currency_symbol' => ['label' => 'Currency symbol', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:5'], 'help' => 'Shown before amounts, e.g. $ or €.'],
                    'billing.tax_label' => ['label' => 'Tax name', 'type' => 'text',  'rules' => ['nullable', 'string', 'max:20'], 'help' => 'For example: Tax, VAT, GST.'],
                    'billing.tax_rate' => ['label' => 'Default tax rate (%)', 'type' => 'text',  'rules' => ['nullable', 'numeric', 'min:0', 'max:100']],
                    'billing.tax_number' => ['label' => 'Tax / registration number', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'help' => 'Printed on quotes and invoices if set.'],
                    'billing.quote_prefix' => ['label' => 'Quote number prefix', 'type' => 'text',  'rules' => ['nullable', 'string', 'max:10', 'alpha_dash:ascii'], 'help' => 'Numbers look like QT-2026-0001.'],
                    'billing.invoice_prefix' => ['label' => 'Invoice number prefix', 'type' => 'text',  'rules' => ['nullable', 'string', 'max:10', 'alpha_dash:ascii']],
                    'billing.quote_valid_days' => ['label' => 'Quotes valid for (days)', 'type' => 'text',  'rules' => ['nullable', 'integer', 'min:1', 'max:365']],
                    'billing.payment_terms_days' => ['label' => 'Payment due after (days)', 'type' => 'text',  'rules' => ['nullable', 'integer', 'min:0', 'max:365']],
                    'billing.payment_details' => ['label' => 'Payment details', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000'], 'help' => 'Bank account or other payment instructions printed on invoices.'],
                    'billing.quote_terms' => ['label' => 'Default quote terms', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                    'billing.invoice_notes' => ['label' => 'Default invoice note', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                ],
            ],
            'social' => [
                'title' => 'Social media',
                'description' => 'Links shown as icons in the footer. Leave empty to hide.',
                'icon' => 'users',
                'fields' => [
                    'social.facebook' => ['label' => 'Facebook', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:300']],
                    'social.instagram' => ['label' => 'Instagram', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:300']],
                    'social.linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:300']],
                    'social.youtube' => ['label' => 'YouTube', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:300']],
                ],
            ],
        ];
    }

    /**
     * Flat list of every field, keyed by setting key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return collect(self::groups())->flatMap(fn (array $group) => $group['fields'])->all();
    }

    /**
     * Current value for a field: the saved setting, else the config default.
     */
    public static function value(string $key): ?string
    {
        return config("zenray.{$key}");
    }

    /**
     * Apply saved settings on top of config('zenray'). Safe to call before
     * the settings table exists (e.g. on a fresh install).
     */
    public static function apply(): void
    {
        try {
            $settings = Setting::allCached();
        } catch (Throwable) {
            return;
        }

        foreach ($settings as $key => $value) {
            if (filled($value) && array_key_exists($key, self::fields())) {
                Config::set("zenray.{$key}", $value);
            }
        }

        // Enquiries fall back to the contact email when no dedicated address is set.
        if (blank(config('zenray.enquiries_to'))) {
            Config::set('zenray.enquiries_to', config('zenray.contact.email'));
        }
    }
}
