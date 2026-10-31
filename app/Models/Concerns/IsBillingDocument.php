<?php

namespace App\Models\Concerns;

use App\Models\LineItem;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared behaviour of quotations and invoices: customer snapshot, line items,
 * totals and sequential numbering (PREFIX-YEAR-0001).
 *
 * Using classes define NUMBER_PREFIX_SETTING (a key under config('zenray.billing')).
 */
trait IsBillingDocument
{
    public const CUSTOMER_FIELDS = ['customer_name', 'customer_organization', 'customer_email', 'customer_phone', 'customer_address'];

    /**
     * @return array<string, string>
     */
    protected function documentCasts(): array
    {
        return [
            'issue_date' => 'date',
            'tax_rate' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @return MorphMany<LineItem, $this>
     */
    public function items(): MorphMany
    {
        return $this->morphMany(LineItem::class, 'document')->orderBy('position')->orderBy('id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The next free number for this year, e.g. INV-2026-0007.
     */
    public static function nextNumber(): string
    {
        $prefix = config('zenray.billing.'.static::NUMBER_PREFIX_SETTING).'-'.now()->year.'-';

        $last = static::query()->where('number', 'like', $prefix.'%')->orderByDesc('id')->value('number');
        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        do {
            $number = $prefix.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (static::query()->where('number', $number)->exists());

        return $number;
    }

    /**
     * Replace the line items and recalculate totals. Rows need description,
     * quantity and unit_price; inventory_item_id and unit are optional.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncItems(array $rows): void
    {
        $this->items()->delete();

        foreach (array_values($rows) as $position => $row) {
            $quantity = Money::round($row['quantity']);
            $price = Money::round($row['unit_price']);

            $this->items()->create([
                'inventory_item_id' => $row['inventory_item_id'] ?? null,
                'description' => trim($row['description']),
                'quantity' => $quantity,
                'unit' => $row['unit'] ?? null,
                'unit_price' => $price,
                'line_total' => Money::round($quantity * $price),
                'position' => $position,
            ]);
        }

        $this->unsetRelation('items');
        $this->recalculate();
    }

    /**
     * Subtotal → minus discount → plus tax on the discounted amount.
     */
    public function recalculate(): void
    {
        $subtotal = Money::round($this->items()->sum('line_total'));
        $discount = min(Money::round($this->discount), $subtotal);
        $tax = Money::round(($subtotal - $discount) * (float) $this->tax_rate / 100);

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_total' => $tax,
            'total' => Money::round($subtotal - $discount + $tax),
        ])->save();
    }

    public function money(string $attribute): string
    {
        return Money::format($this->{$attribute}, $this->currency);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(fn ($q) => $q
            ->where('number', 'like', "%{$term}%")
            ->orWhere('customer_name', 'like', "%{$term}%")
            ->orWhere('customer_organization', 'like', "%{$term}%")
            ->orWhere('customer_email', 'like', "%{$term}%"));
    }
}
