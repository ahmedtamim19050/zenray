<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /**
     * Options for "What are you interested in?", keyed by slug.
     *
     * @return array<string, string>
     */
    public static function interestOptions(): array
    {
        return collect(config('zenray.product_categories'))
            ->merge(config('zenray.services'))
            ->mapWithKeys(fn (array $item) => [$item['slug'] => $item['name']])
            ->put('other', 'Something else / not sure yet')
            ->all();
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Send visitors back to the form itself when validation fails.
     */
    protected function getRedirectUrl(): string
    {
        return strtok(url()->previous(), '#').'#enquiry';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:160'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:180'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:40', 'regex:/^[\d\s+()\-.]{6,40}$/'],
            'interest' => ['required', Rule::in(array_keys(self::interestOptions()))],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'type' => ['nullable', Rule::in(['quote', 'contact'])],
            // Honeypot: real visitors never see or fill this field.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => 'Please give us an email address or a phone number so we can reply.',
            'phone.required_without' => 'Please give us a phone number or an email address so we can reply.',
            'phone.regex' => 'Please enter a phone number using digits, spaces and + ( ) - only.',
            'interest.required' => 'Please choose what you are interested in. "Not sure yet" is fine.',
            'message.min' => 'Please tell us a little more (at least 10 characters).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'organization' => 'organization or facility',
            'interest' => 'area of interest',
        ];
    }
}
