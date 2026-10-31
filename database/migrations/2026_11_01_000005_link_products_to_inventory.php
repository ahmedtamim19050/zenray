<?php

use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * Give every existing website product a stock record in inventory.
 * New products get one automatically (see Product::booted()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Product::query()->whereDoesntHave('inventoryItem')->orderBy('sort_order')->get()
            ->each(fn (Product $product) => InventoryItem::createForProduct($product));
    }

    public function down(): void
    {
        // Stock records are kept: they may already have movements and invoices.
    }
};
