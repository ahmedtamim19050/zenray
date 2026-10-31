<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'sku', 'name', 'description', 'unit', 'cost_price', 'sale_price', 'reorder_level', 'location', 'is_active'])]
class InventoryItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'quantity' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Create the stock record for a website product (no stock, prices to be set).
     */
    public static function createForProduct(Product $product): self
    {
        return static::create([
            'product_id' => $product->id,
            'sku' => static::uniqueSku($product->slug ?: $product->name),
            'name' => $product->name,
            'unit' => 'pcs',
            'cost_price' => 0,
            'sale_price' => 0,
            'reorder_level' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * An unused SKU based on a name or slug, e.g. MOBILE-X-RAY-UNIT or MOBILE-X-RAY-UNIT-2.
     */
    public static function uniqueSku(string $base): string
    {
        $base = Str::upper(Str::limit(Str::slug($base), 50, '')) ?: 'ITEM';
        $sku = $base;

        for ($i = 2; static::query()->where('sku', $sku)->exists(); $i++) {
            $sku = "{$base}-{$i}";
        }

        return $sku;
    }

    public function needsPricing(): bool
    {
        return (float) $this->sale_price <= 0;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    /**
     * Change the stock level and log the movement. Positive quantities add
     * stock, negative ones remove it. Runs inside a row lock.
     *
     * @param  array{user_id?: int|null, invoice_id?: int|null, reference?: string|null, note?: string|null, unit_cost?: float|string|null}  $context
     */
    public function adjust(int $quantity, string $type, array $context = []): StockMovement
    {
        return DB::transaction(function () use ($quantity, $type, $context) {
            $item = static::query()->lockForUpdate()->findOrFail($this->id);
            $item->quantity += $quantity;
            $item->save();

            $this->quantity = $item->quantity;

            return $item->movements()->create($context + [
                'type' => $type,
                'quantity' => $quantity,
                'balance' => $item->quantity,
            ]);
        });
    }

    /**
     * Set the stock to a counted figure, logging the difference.
     *
     * @param  array<string, mixed>  $context
     */
    public function countAs(int $counted, array $context = []): ?StockMovement
    {
        $difference = $counted - $this->quantity;

        return $difference === 0 ? null : $this->adjust($difference, 'counted', $context);
    }

    public function isLow(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function stockLabel(): string
    {
        return match (true) {
            $this->quantity <= 0 => 'Out of stock',
            $this->isLow() => 'Low stock',
            default => 'In stock',
        };
    }

    public function stockClasses(): string
    {
        return match (true) {
            $this->quantity <= 0 => 'bg-red-50 text-red-700 ring-red-200',
            $this->isLow() => 'bg-signal-50 text-signal-700 ring-signal-200',
            default => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        };
    }

    public function stockValue(): float
    {
        return Money::round(max(0, $this->quantity) * (float) $this->cost_price);
    }

    /**
     * Gross margin on the sale price, as a percentage.
     */
    public function margin(): ?float
    {
        $price = (float) $this->sale_price;

        return $price > 0 ? round(($price - (float) $this->cost_price) / $price * 100, 1) : null;
    }

    /**
     * @param  Builder<InventoryItem>  $query
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    /**
     * @param  Builder<InventoryItem>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")->orWhere('location', 'like', "%{$term}%"));
    }
}
