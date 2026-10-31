{{--
    Quote / invoice editor: customer, dates, line items with live totals.
    Line items can be picked from inventory (fills description, unit and price)
    or typed freely for services. Behaviour lives in app.js ([data-doc-editor]).
--}}
@props(['document', 'type', 'customers', 'inventory', 'items', 'action', 'method' => 'POST'])

@php
    $isQuote = $type === 'quote';
    $dateField = $isQuote ? 'valid_until' : 'due_date';
    $input = 'block w-full rounded-xl border-0 bg-white px-3.5 py-2.5 text-[0.9375rem] text-navy-900 shadow-sm ring-1 ring-navy-200 placeholder:text-slate-400 transition focus:ring-2 focus:ring-navy-700 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600';
    $label = 'mb-1.5 block text-sm font-semibold text-navy-800';
    $val = fn (string $field) => old($field, $document->{$field} instanceof \DateTimeInterface ? $document->{$field}->format('Y-m-d') : $document->{$field});
    $err = fn (string $field) => $errors->first($field);
    $taxLabel = config('zenray.billing.tax_label');
@endphp

<form method="POST" action="{{ $action }}" novalidate data-doc-editor data-inventory='@json($inventory)' data-currency-symbol="{{ \App\Support\Money::symbol($val('currency')) }}" data-number-locale="{{ in_array(strtoupper((string) $val('currency')), \App\Support\Money::LAKH_GROUPED, true) ? 'en-IN' : 'en' }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($document->enquiry_id ?? null) <input type="hidden" name="enquiry_id" value="{{ $document->enquiry_id }}"> @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">
            <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }}.</p>
            @if ($errors->has('items')) <p class="mt-1">{{ $errors->first('items') }}</p> @endif
        </div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-3">
        {{-- Customer --}}
        <x-dash.card title="Customer" class="xl:col-span-2">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="doc-user" class="{{ $label }}">Customer account <span class="font-normal text-slate-500">(optional)</span></label>
                    <div class="relative">
                        <select id="doc-user" name="user_id" class="{{ $input }} appearance-none pr-11" data-customer-picker>
                            <option value="">Not linked to an account</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string) $val('user_id') === (string) $customer->id)
                                        data-name="{{ $customer->name }}" data-organization="{{ $customer->organization }}" data-email="{{ $customer->email }}" data-phone="{{ $customer->phone }}">
                                    {{ $customer->name }}{{ $customer->organization ? ' · '.$customer->organization : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-navy-700" />
                    </div>
                    <p class="mt-1.5 text-sm text-slate-500">Choosing an account fills in the details below.</p>
                </div>

                @foreach (['customer_name' => ['Name', 'text', true], 'customer_organization' => ['Organization', 'text', false], 'customer_email' => ['Email', 'email', false], 'customer_phone' => ['Phone', 'tel', false]] as $field => [$text, $inputType, $required])
                    <div>
                        <label for="doc-{{ $field }}" class="{{ $label }}">{{ $text }} @if ($required)<span class="text-red-700" aria-hidden="true">*</span>@endif</label>
                        <input type="{{ $inputType }}" id="doc-{{ $field }}" name="{{ $field }}" value="{{ $val($field) }}" @required($required)
                               class="{{ $input }}" data-customer-field="{{ Str::after($field, 'customer_') }}" @if ($err($field)) aria-invalid="true" @endif>
                        @if ($err($field)) <p class="mt-1.5 text-sm text-red-700">{{ $err($field) }}</p> @endif
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label for="doc-address" class="{{ $label }}">Billing address</label>
                    <textarea id="doc-address" name="customer_address" rows="2" class="{{ $input }} resize-y">{{ $val('customer_address') }}</textarea>
                </div>
            </div>
        </x-dash.card>

        {{-- Details --}}
        <x-dash.card title="Details">
            <div class="grid gap-5">
                <div>
                    <p class="{{ $label }}">Number</p>
                    <p class="font-mono text-sm text-slate-600">{{ $document->number ?? 'Assigned when saved' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="doc-issue" class="{{ $label }}">{{ $isQuote ? 'Quote date' : 'Invoice date' }}</label>
                        <input type="date" id="doc-issue" name="issue_date" value="{{ $val('issue_date') }}" required class="{{ $input }}" @if ($err('issue_date')) aria-invalid="true" @endif>
                    </div>
                    <div>
                        <label for="doc-{{ $dateField }}" class="{{ $label }}">{{ $isQuote ? 'Valid until' : 'Due date' }}</label>
                        <input type="date" id="doc-{{ $dateField }}" name="{{ $dateField }}" value="{{ $val($dateField) }}" class="{{ $input }}" @if ($err($dateField)) aria-invalid="true" @endif>
                    </div>
                </div>
                @foreach (['issue_date', $dateField] as $field) @if ($err($field)) <p class="-mt-3 text-sm text-red-700">{{ $err($field) }}</p> @endif @endforeach
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="doc-currency" class="{{ $label }}">Currency</label>
                        <input type="text" id="doc-currency" name="currency" value="{{ $val('currency') }}" maxlength="3" required class="{{ $input }} font-mono uppercase" @if ($err('currency')) aria-invalid="true" @endif>
                    </div>
                    <div>
                        <label for="doc-tax" class="{{ $label }}">{{ $taxLabel }} rate (%)</label>
                        <input type="number" id="doc-tax" name="tax_rate" value="{{ (float) $val('tax_rate') }}" min="0" max="100" step="0.01" required class="{{ $input }}" data-tax-rate @if ($err('tax_rate')) aria-invalid="true" @endif>
                    </div>
                </div>
                @foreach (['currency', 'tax_rate'] as $field) @if ($err($field)) <p class="-mt-3 text-sm text-red-700">{{ $err($field) }}</p> @endif @endforeach
            </div>
        </x-dash.card>

        {{-- Line items --}}
        <x-dash.card title="Items" class="xl:col-span-3" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="w-56 px-4 py-3 pl-6">Inventory item</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="w-24 px-4 py-3">Qty</th>
                            <th scope="col" class="w-24 px-4 py-3">Unit</th>
                            <th scope="col" class="w-36 px-4 py-3">Unit price</th>
                            <th scope="col" class="w-32 px-4 py-3 text-right">Amount</th>
                            <th scope="col" class="w-12 px-4 py-3 pr-6"><span class="sr-only">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50" data-rows>
                        @foreach ($items as $index => $row)
                            @include('admin.documents.line-row', ['index' => $index, 'row' => $row, 'inventory' => $inventory, 'input' => $input])
                        @endforeach
                    </tbody>
                </table>
            </div>
            <template data-row-template>
                @include('admin.documents.line-row', ['index' => '__INDEX__', 'row' => [], 'inventory' => $inventory, 'input' => $input])
            </template>

            <div class="flex flex-col gap-6 border-t border-navy-100 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                <button type="button" class="inline-flex h-10 items-center gap-2 self-start rounded-full bg-navy-50 px-4 text-sm font-semibold text-navy-800 ring-1 ring-navy-100 transition hover:bg-navy-100" data-add-row>
                    <span aria-hidden="true">+</span> Add line
                </button>

                <dl class="w-full max-w-sm space-y-2.5 text-sm" aria-live="polite">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-navy-900 tabular-nums" data-subtotal>—</dd></div>
                    <div class="flex items-center justify-between gap-4">
                        <dt><label for="doc-discount" class="text-slate-500">Discount</label></dt>
                        <dd><input type="number" id="doc-discount" name="discount" value="{{ (float) $val('discount') }}" min="0" step="0.01" class="{{ $input }} w-36 py-1.5 text-right tabular-nums" data-discount></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ $taxLabel }} (<span data-tax-label>{{ (float) $val('tax_rate') }}</span>%)</dt><dd class="font-semibold text-navy-900 tabular-nums" data-tax>—</dd></div>
                    <div class="flex justify-between border-t border-navy-100 pt-3 text-base"><dt class="font-bold text-navy-900">Total</dt><dd class="font-bold text-navy-900 tabular-nums" data-total>—</dd></div>
                </dl>
            </div>
        </x-dash.card>

        {{-- Notes --}}
        <x-dash.card title="Notes & terms" class="xl:col-span-3">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="doc-notes" class="{{ $label }}">Notes for the customer</label>
                    <textarea id="doc-notes" name="notes" rows="4" class="{{ $input }} resize-y">{{ $val('notes') }}</textarea>
                </div>
                <div>
                    <label for="doc-terms" class="{{ $label }}">Terms</label>
                    <textarea id="doc-terms" name="terms" rows="4" class="{{ $input }} resize-y">{{ $val('terms') }}</textarea>
                </div>
            </div>
        </x-dash.card>
    </div>

    <div class="sticky bottom-4 z-10 mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-navy-900 p-4 shadow-2xl sm:px-6">
        <p class="text-sm text-navy-200">Total <span class="ml-1 text-lg font-bold text-white tabular-nums" data-total>—</span></p>
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() }}" class="text-sm font-semibold text-navy-200 hover:text-white">Cancel</a>
            <button type="submit" class="btn-shine inline-flex h-11 shrink-0 items-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                {{ $document->exists ? 'Save changes' : 'Save as draft' }}
            </button>
        </div>
    </div>
</form>
