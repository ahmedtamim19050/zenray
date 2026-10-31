<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sample prices (BDT) and stock levels for the website products, plus common
 * accessories and spare parts sold alongside them.
 *
 * These are example figures for trying out quotes, invoices and reports.
 * Replace them with your real costs, prices and counts in Admin → Inventory.
 * Safe to run more than once: items are matched by SKU and stock is only
 * set for items that have no stock history yet.
 *
 * Run with: php artisan db:seed --class=InventorySeeder
 */
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Website products: [product name, cost, sale price, stock, reorder level, location, description]
        $products = [
            ['Mobile X-ray unit', 2_450_000, 2_950_000, 2, 1, 'Main warehouse · Bay A', 'Mobile digital X-ray system with motorised drive and folding tube arm.'],
            ['Mobile C-arm', 6_800_000, 7_900_000, 1, 1, 'Main warehouse · Bay A', 'Mobile C-arm for fluoroscopy in theatres and procedure rooms.'],
            ['Digital X-ray room', 9_500_000, 11_500_000, 1, 0, 'Main warehouse · Bay B', 'Complete room set: patient table, ceiling or floor tube stand, wall stand, generator and DR detector.'],
            ['Wall-stand detector', 1_350_000, 1_650_000, 3, 1, 'Main warehouse · Rack C1', 'Vertical wall stand with flat-panel DR detector for chest and standing exams.'],
            ['Protective lead apron', 9_500, 14_500, 25, 10, 'Accessories store · Shelf D2', '0.5 mm Pb equivalent wraparound apron, medium size.'],
            ['Ultrasound system', 3_200_000, 3_850_000, 2, 1, 'Main warehouse · Bay A', 'Cart-based ultrasound system with convex and linear probes.'],
        ];

        foreach ($products as [$name, $cost, $price, $stock, $reorder, $location, $description]) {
            $product = Product::query()->where('name', $name)->first();

            $item = $product
                ? ($product->inventoryItem ?? InventoryItem::createForProduct($product))
                : InventoryItem::query()->firstOrNew(['sku' => InventoryItem::uniqueSku($name)], ['name' => $name, 'unit' => 'pcs']);

            $this->apply($item, compact('cost', 'price', 'stock', 'reorder', 'location', 'description'));
        }

        // Accessories and spare parts (not shown on the website): [SKU, name, unit, cost, sale price, stock, reorder level, location]
        $accessories = [
            ['ACC-THY-COLLAR', 'Thyroid collar, 0.5 mm Pb', 'pcs', 2_200, 3_500, 30, 10, 'Accessories store · Shelf D2'],
            ['ACC-LEAD-GLOVES', 'Lead gloves, 0.5 mm Pb (pair)', 'pair', 6_500, 9_800, 8, 4, 'Accessories store · Shelf D2'],
            ['ACC-LEAD-GLASSES', 'Lead glasses, 0.75 mm Pb', 'pcs', 12_000, 18_500, 6, 3, 'Accessories store · Shelf D3'],
            ['ACC-MARKER-LR', 'X-ray L/R marker set', 'set', 450, 900, 60, 20, 'Accessories store · Drawer E1'],
            ['ACC-POS-SPONGE', 'Positioning sponge set (8 pcs)', 'set', 3_800, 6_200, 12, 5, 'Accessories store · Shelf D4'],
            ['ACC-APRON-RACK', 'Wall-mounted apron rack (5 hooks)', 'pcs', 4_500, 7_500, 7, 3, 'Accessories store · Shelf D5'],
            ['ACC-CASSETTE-HLD', 'Mobile cassette / detector holder', 'pcs', 18_000, 27_500, 4, 2, 'Main warehouse · Rack C2'],
            ['PART-DET-BATTERY', 'Wireless detector battery pack', 'pcs', 28_000, 42_000, 5, 2, 'Parts store · Bin P1'],
            ['PART-DET-CHARGER', 'Detector battery charger', 'pcs', 15_000, 23_000, 3, 2, 'Parts store · Bin P1'],
            ['PART-HAND-SWITCH', 'Exposure hand switch, 2-stage', 'pcs', 6_000, 9_500, 1, 2, 'Parts store · Bin P2'],
            ['PART-COLL-LAMP', 'Collimator lamp 24V 150W', 'pcs', 1_800, 3_200, 15, 6, 'Parts store · Bin P3'],
            ['IT-DICOM-MONITOR', 'Medical grade diagnostic monitor, 21"', 'pcs', 185_000, 245_000, 2, 1, 'Main warehouse · Rack C3'],
        ];
        // Services (installation, training, maintenance) are not stocked: add them as
        // "Service / custom line" rows on quotes and invoices instead.

        foreach ($accessories as [$sku, $name, $unit, $cost, $price, $stock, $reorder, $location]) {
            $item = InventoryItem::query()->firstOrNew(['sku' => $sku], ['name' => $name, 'unit' => $unit]);
            $this->apply($item, ['cost' => $cost, 'price' => $price, 'stock' => $stock, 'reorder' => $reorder, 'location' => $location]);
        }
    }

    /**
     * @param  array{cost: int, price: int, stock: int, reorder: int, location: ?string, description?: string}  $values
     */
    private function apply(InventoryItem $item, array $values): void
    {
        $item->fill([
            'cost_price' => $values['cost'],
            'sale_price' => $values['price'],
            'reorder_level' => $values['reorder'],
            'location' => $values['location'],
            'description' => $values['description'] ?? $item->description,
            'is_active' => true,
        ])->save();

        // Only set opening stock once, so real stock movements are never overwritten.
        if ($values['stock'] > 0 && ! $item->movements()->exists()) {
            $item->adjust($values['stock'], 'opening', ['unit_cost' => $values['cost'], 'note' => 'Opening stock (sample data)']);
        }
    }
}
