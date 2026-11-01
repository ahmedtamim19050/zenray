{{-- One editable line item row. $index is "__INDEX__" in the JS template. --}}
@php
    $name = fn (string $field) => "items[{$index}][{$field}]";
    $error = fn (string $field) => is_int($index) || ctype_digit((string) $index) ? $errors->first("items.{$index}.{$field}") : null;
    $cell = $input.' py-2';
@endphp

<tr class="align-top" data-row>
    <td class="px-4 py-3 pl-6">
        <label class="sr-only" for="item-{{ $index }}-inventory">Product or service</label>
        <select id="item-{{ $index }}-inventory" name="{{ $name('inventory_item_id') }}" class="{{ $cell }} pr-8 text-sm" data-item-picker>
            <option value="">Custom line</option>
            @if ($services->isNotEmpty())
                <optgroup label="Services">
                    @foreach ($services as $service)
                        <option value="" data-service="{{ $service }}" @selected(blank($row['inventory_item_id'] ?? null) && ($row['description'] ?? null) === $service)>{{ $service }}</option>
                    @endforeach
                </optgroup>
            @endif
            <optgroup label="Inventory">
                @foreach ($inventory as $item)
                    <option value="{{ $item['id'] }}" @selected((string) ($row['inventory_item_id'] ?? '') === (string) $item['id'])>{{ $item['label'] }} ({{ $item['stock'] }} in stock)</option>
                @endforeach
            </optgroup>
        </select>
    </td>
    <td class="px-4 py-3">
        <label class="sr-only" for="item-{{ $index }}-description">Description</label>
        <textarea id="item-{{ $index }}-description" name="{{ $name('description') }}" rows="1" class="{{ $cell }} min-h-10 resize-y" placeholder="What are you supplying?"
                  data-item-description @if ($error('description')) aria-invalid="true" @endif>{{ $row['description'] ?? '' }}</textarea>
        @if ($error('description')) <p class="mt-1 text-xs text-red-700">{{ $error('description') }}</p> @endif
    </td>
    <td class="px-4 py-3">
        <label class="sr-only" for="item-{{ $index }}-quantity">Quantity</label>
        <input type="number" id="item-{{ $index }}-quantity" name="{{ $name('quantity') }}" value="{{ isset($row['quantity']) ? (float) $row['quantity'] : 1 }}" min="0.01" step="0.01"
               class="{{ $cell }} tabular-nums" data-item-quantity @if ($error('quantity')) aria-invalid="true" @endif>
        @if ($error('quantity')) <p class="mt-1 text-xs text-red-700">{{ $error('quantity') }}</p> @endif
    </td>
    <td class="px-4 py-3">
        <label class="sr-only" for="item-{{ $index }}-unit">Unit</label>
        <input type="text" id="item-{{ $index }}-unit" name="{{ $name('unit') }}" value="{{ $row['unit'] ?? '' }}" maxlength="20" placeholder="pcs" class="{{ $cell }}" data-item-unit>
    </td>
    <td class="px-4 py-3">
        <label class="sr-only" for="item-{{ $index }}-price">Unit price</label>
        <input type="number" id="item-{{ $index }}-price" name="{{ $name('unit_price') }}" value="{{ isset($row['unit_price']) && $row['unit_price'] !== '' ? (float) $row['unit_price'] : '' }}" min="0" step="0.01"
               class="{{ $cell }} tabular-nums" placeholder="0.00" data-item-price @if ($error('unit_price')) aria-invalid="true" @endif>
        @if ($error('unit_price')) <p class="mt-1 text-xs text-red-700">{{ $error('unit_price') }}</p> @endif
    </td>
    <td class="px-4 py-3 pt-5 text-right font-semibold text-navy-900 tabular-nums" data-item-total>—</td>
    <td class="px-4 py-3 pr-6">
        <button type="button" class="flex size-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-red-50 hover:text-red-700" data-remove-row>
            <x-icon name="close" class="size-4" /><span class="sr-only">Remove line</span>
        </button>
    </td>
</tr>
