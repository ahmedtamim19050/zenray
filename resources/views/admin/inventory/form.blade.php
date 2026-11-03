@php
    $editing = $item->exists;
    $input = 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-[0.9375rem] text-navy-900 shadow-sm ring-1 ring-navy-200 placeholder:text-slate-400 transition focus:ring-2 focus:ring-navy-700 focus:outline-none aria-invalid:ring-2 aria-invalid:ring-red-600';
    $label = 'mb-1.5 block text-sm font-semibold text-navy-800';
    $field = function (string $name, string $text, string $type = 'text', array $extra = []) use ($item, $input, $label, $errors) {
        return compact('name', 'text', 'type', 'extra') + ['value' => old($name, $item->{$name}), 'error' => $errors->first($name)];
    };
    $fields = [
        $field('name', 'Item name', 'text', ['required' => true]),
        $field('sku', 'SKU / item code', 'text', ['required' => true, 'help' => 'Your unique code for this item, e.g. DR-1417-W.', 'class' => 'font-mono uppercase']),
        $field('unit', 'Unit', 'text', ['required' => true, 'help' => 'How it is counted, e.g. pcs, set, box.']),
        $field('location', 'Storage location', 'text', ['help' => 'Warehouse, shelf or bin.']),
        $field('cost_price', 'Cost price', 'number', ['required' => true, 'step' => '0.01', 'help' => 'What you pay per unit.']),
        $field('sale_price', 'Sale price', 'number', ['required' => true, 'step' => '0.01', 'help' => 'Default price on quotes and invoices.']),
        $field('reorder_level', 'Reorder level', 'number', ['step' => '1', 'help' => 'You are alerted when stock falls to this level.']),
    ];
@endphp

<x-layouts.dashboard :title="$editing ? 'Edit '.$item->name : 'Add inventory item'" area="admin">
    <x-slot:subtitle>
        <a href="{{ $editing ? route('admin.inventory.show', $item) : route('admin.inventory.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← {{ $editing ? 'Back to item' : 'Inventory' }}</a>
    </x-slot:subtitle>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">
            <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.inventory.update', $item) : route('admin.inventory.store') }}" class="max-w-4xl" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-dash.card>
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($fields as $f)
                    <div>
                        <label for="inv-{{ $f['name'] }}" class="{{ $label }}">{{ $f['text'] }} @if ($f['extra']['required'] ?? false)<span class="text-red-700" aria-hidden="true">*</span>@endif</label>
                        <input type="{{ $f['type'] }}" id="inv-{{ $f['name'] }}" name="{{ $f['name'] }}" value="{{ $f['value'] }}" @required($f['extra']['required'] ?? false)
                               @if ($f['type'] === 'number') min="0" step="{{ $f['extra']['step'] }}" inputmode="decimal" @endif
                               class="{{ $input }} {{ $f['extra']['class'] ?? '' }}" @if ($f['error']) aria-invalid="true" aria-describedby="inv-{{ $f['name'] }}-error" @endif>
                        @if ($f['extra']['help'] ?? null) <p class="mt-1.5 text-sm text-slate-500">{{ $f['extra']['help'] }}</p> @endif
                        @if ($f['error']) <p id="inv-{{ $f['name'] }}-error" class="mt-1.5 text-sm text-red-700">{{ $f['error'] }}</p> @endif
                    </div>
                @endforeach

                @unless ($editing)
                    <div>
                        <label for="inv-opening" class="{{ $label }}">Opening stock</label>
                        <input type="number" id="inv-opening" name="opening_stock" value="{{ old('opening_stock', 0) }}" min="0" step="1" class="{{ $input }}">
                        <p class="mt-1.5 text-sm text-slate-500">How many you have right now. Later changes go through “Adjust stock”.</p>
                        @error('opening_stock') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                @endunless

                <div class="sm:col-span-2">
                    <label for="inv-description" class="{{ $label }}">Description</label>
                    <textarea id="inv-description" name="description" rows="3" class="{{ $input }} resize-y" placeholder="Model, specification or notes printed on quotes">{{ old('description', $item->description) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="inv-product" class="{{ $label }}">Linked website product <span class="font-normal text-slate-500">(optional)</span></label>
                    <select id="inv-product" name="product_id" class="{{ $input }}">
                        <option value="">Not linked</option>
                        @foreach ($products as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('product_id', $item->product_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-navy-100">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="mt-0.5 size-5 accent-navy-800">
                        <span>
                            <span class="block text-sm font-semibold text-navy-800">Active</span>
                            <span class="block text-sm text-slate-500">Inactive items are hidden from the item picker on quotes and invoices.</span>
                        </span>
                    </label>
                </div>
            </div>
        </x-dash.card>

        <div class="sticky bottom-4 z-10 mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-navy-900 p-4 shadow-2xl sm:px-6">
            <a href="{{ $editing ? route('admin.inventory.show', $item) : route('admin.inventory.index') }}" class="text-sm font-semibold text-navy-200 hover:text-white">Cancel</a>
            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">{{ $editing ? 'Save changes' : 'Add item' }}</button>
        </div>
    </form>
</x-layouts.dashboard>
