<?php

namespace App\Models;

use App\Models\Concerns\IsSiteContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['equipment_category_id', 'name', 'slug', 'photo', 'alt', 'summary', 'features', 'availability', 'is_example', 'sort_order', 'is_active'])]
class Product extends Model
{
    use IsSiteContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['features' => 'array', 'is_example' => 'boolean', 'is_active' => 'boolean'];
    }

    /**
     * Every website product has a stock record in inventory, kept in step with its name.
     */
    protected static function booted(): void
    {
        static::created(fn (Product $product) => InventoryItem::createForProduct($product));

        static::updated(function (Product $product) {
            if ($product->wasChanged('name')) {
                $product->inventoryItem?->update(['name' => $product->name]);
            }
        });
    }

    /**
     * @return HasOne<InventoryItem, $this>
     */
    public function inventoryItem(): HasOne
    {
        return $this->hasOne(InventoryItem::class);
    }

    /**
     * @return BelongsTo<EquipmentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }
}
