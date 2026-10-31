<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the quote and invoice editors (customer, dates, line items).
 */
class SaveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Drop completely empty item rows before validating.
     */
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn ($row) => is_array($row) && (filled($row['description'] ?? null) || filled($row['unit_price'] ?? null)))
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isQuote = $this->routeIs('admin.quotes.*');

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_organization' => ['nullable', 'string', 'max:160'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'issue_date' => ['required', 'date'],
            $isQuote ? 'valid_until' : 'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'terms' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one line item.',
            'items.*.description.required' => 'Each line needs a description.',
            'items.*.quantity.gt' => 'Quantities must be more than zero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'customer name',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'price',
            'items.*.description' => 'description',
        ];
    }

    /**
     * Document attributes (everything except the line items), with uppercase currency.
     *
     * @return array<string, mixed>
     */
    public function document(): array
    {
        $data = collect($this->validated())->except('items')->all();
        $data['currency'] = strtoupper($data['currency']);
        $data['discount'] ??= 0;

        return $data;
    }
}
