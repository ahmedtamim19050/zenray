<?php

use App\Models\Enquiry;
use App\Models\EquipmentCategory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Service;
use App\Models\User;

function salesAdmin(): User
{
    return User::factory()->admin()->create();
}

function stockItem(int $quantity = 10, array $attributes = []): InventoryItem
{
    $item = InventoryItem::create(array_merge(['sku' => 'DR-'.fake()->unique()->numerify('####'), 'name' => 'Wireless DR detector', 'unit' => 'pcs', 'cost_price' => 6000, 'sale_price' => 9000, 'reorder_level' => 2], $attributes));
    $item->adjust($quantity, 'opening');

    return $item;
}

function documentPayload(array $items, array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'Dr. Amina Rahman',
        'customer_organization' => 'City Imaging Centre',
        'customer_email' => 'amina@example.com',
        'issue_date' => today()->toDateString(),
        'currency' => 'bdt',
        'tax_rate' => 10,
        'discount' => 100,
        'items' => $items,
    ], $overrides);
}

test('sales pages are for admins only', function (string $uri) {
    $this->get($uri)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get($uri)->assertForbidden();
    $this->actingAs(salesAdmin())->get($uri)->assertOk();
})->with(['/admin/quotes', '/admin/quotes/create', '/admin/invoices', '/admin/invoices/create', '/admin/inventory', '/admin/inventory/create', '/admin/reports']);

test('inventory items are created with opening stock and adjusted', function () {
    $admin = salesAdmin();

    $this->actingAs($admin)->post('/admin/inventory', [
        'sku' => 'apron-m', 'name' => 'Lead apron, medium', 'unit' => 'pcs', 'cost_price' => 80, 'sale_price' => 140,
        'reorder_level' => 3, 'opening_stock' => 5, 'is_active' => '1',
    ])->assertSessionHasNoErrors();

    $item = InventoryItem::where('sku', 'APRON-M')->firstOrFail();
    expect($item->quantity)->toBe(5)->and($item->movements()->first()->type)->toBe('opening');

    $this->actingAs($admin)->post("/admin/inventory/{$item->id}/adjust", ['type' => 'received', 'quantity' => 10, 'unit_cost' => 85]);
    $this->actingAs($admin)->post("/admin/inventory/{$item->id}/adjust", ['type' => 'removed', 'quantity' => 2, 'note' => 'Damaged']);
    $this->actingAs($admin)->post("/admin/inventory/{$item->id}/adjust", ['type' => 'counted', 'quantity' => 12]);

    $item->refresh();
    expect($item->quantity)->toBe(12)
        ->and((float) $item->cost_price)->toBe(85.0)
        ->and($item->movements()->pluck('balance')->all())->toBe([12, 13, 15, 5]);

    $this->actingAs($admin)->get("/admin/inventory/{$item->id}")->assertOk()->assertSee('Stock count')->assertSee('Damaged');
});

test('low stock items are flagged', function () {
    stockItem(1, ['name' => 'Detector holder']);
    stockItem(50, ['name' => 'Positioning sponge']);

    $this->actingAs(salesAdmin())->get('/admin/inventory?stock=low')->assertSee('Detector holder')->assertDontSee('Positioning sponge');
});

test('a quote is numbered and totals are calculated', function () {
    $item = stockItem();

    $this->actingAs(salesAdmin())->post('/admin/quotes', documentPayload([
        ['inventory_item_id' => $item->id, 'description' => 'Wireless DR detector', 'quantity' => 2, 'unit_price' => 9000],
        ['description' => 'Installation and training', 'quantity' => 1, 'unit_price' => 1500],
        ['description' => '', 'quantity' => 1, 'unit_price' => ''], // empty rows are ignored
    ]))->assertSessionHasNoErrors();

    $quote = Quote::firstOrFail();
    expect($quote->number)->toBe('QT-'.now()->year.'-0001')
        ->and($quote->currency)->toBe('BDT')
        ->and($quote->items)->toHaveCount(2)
        ->and((float) $quote->subtotal)->toBe(19500.0)
        ->and((float) $quote->tax_total)->toBe(1940.0)   // 10% of 19,400 after discount
        ->and((float) $quote->total)->toBe(21340.0);

    $this->actingAs(salesAdmin())->get("/admin/quotes/{$quote->number}")->assertOk()->assertSee('Installation and training');
    $this->actingAs(salesAdmin())->get("/admin/quotes/{$quote->number}/print")->assertOk()->assertSee('Quotation')->assertSee('৳21,340.00');
});

test('documents need a customer and at least one valid line', function () {
    $this->actingAs(salesAdmin())->post('/admin/invoices', documentPayload([['description' => '', 'quantity' => 0, 'unit_price' => '']], ['customer_name' => '']))
        ->assertSessionHasErrors(['customer_name', 'items']);

    $this->actingAs(salesAdmin())->post('/admin/invoices', documentPayload([['description' => 'Service', 'quantity' => 0, 'unit_price' => 10]]))
        ->assertSessionHasErrors('items.0.quantity');
});

test('quote and invoice editors offer active services as line items', function (string $uri) {
    Service::create(['name' => 'Site survey', 'slug' => 'site-survey']);
    Service::create(['name' => 'Retired service', 'slug' => 'retired-service', 'is_active' => false]);

    $this->actingAs(salesAdmin())->get($uri)->assertOk()
        ->assertSee('data-service="Site survey"', false)
        ->assertDontSee('Retired service');
})->with(['/admin/quotes/create', '/admin/invoices/create']);

test('a saved service line reselects its service and never touches stock', function () {
    $admin = salesAdmin();
    Service::create(['name' => 'Site survey', 'slug' => 'site-survey']);

    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['inventory_item_id' => '', 'description' => 'Site survey', 'quantity' => 1, 'unit_price' => 5000]]))
        ->assertSessionHasNoErrors();
    $invoice = Invoice::firstOrFail();

    $this->actingAs($admin)->get("/admin/invoices/{$invoice->number}/edit")->assertOk()
        ->assertSee('data-service="Site survey" selected', false);

    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/issue")->assertSessionHasNoErrors();

    expect($invoice->fresh()->status)->toBe('issued')->and($invoice->stockMovements()->count())->toBe(0);
});

function customerInvoice(User $customer, array $attributes = []): Invoice
{
    $invoice = Invoice::create(array_merge([
        'number' => Invoice::nextNumber(),
        'user_id' => $customer->id,
        'status' => 'issued',
        'customer_name' => $customer->name,
        'issue_date' => today(),
        'due_date' => today()->addDays(14),
        'currency' => 'BDT',
        'tax_rate' => 0,
        'discount' => 0,
    ], $attributes));
    $invoice->syncItems([['description' => 'Site survey', 'quantity' => 1, 'unit_price' => 5000]]);

    return $invoice;
}

test('customers see their own issued invoices on the dashboard and invoice list', function (string $uri) {
    $customer = User::factory()->create();
    $own = customerInvoice($customer);
    $draft = customerInvoice($customer, ['status' => 'draft']);
    $other = customerInvoice(User::factory()->create());

    $this->actingAs($customer)->get($uri)->assertOk()
        ->assertSee($own->number)
        ->assertDontSee($draft->number)
        ->assertDontSee($other->number);
})->with(['/dashboard', '/dashboard/invoices']);

test('customers cannot open draft invoices or invoices of other customers', function (string $suffix) {
    $customer = User::factory()->create();
    $draft = customerInvoice($customer, ['status' => 'draft']);
    $other = customerInvoice(User::factory()->create());

    $this->actingAs($customer)->get("/dashboard/invoices/{$draft->number}{$suffix}")->assertNotFound();
    $this->actingAs($customer)->get("/dashboard/invoices/{$other->number}{$suffix}")->assertNotFound();
})->with(['', '/print']);

test('invoice pages require signing in', function () {
    $invoice = customerInvoice(User::factory()->create());

    $this->get('/dashboard/invoices')->assertRedirect(route('login'));
    $this->get("/dashboard/invoices/{$invoice->number}")->assertRedirect(route('login'));
});

test('a customer invoice shows payments without internal notes or admin links', function () {
    $customer = User::factory()->create();
    $invoice = customerInvoice($customer);
    $invoice->recordPayment(['amount' => 2000, 'paid_on' => today()->toDateString(), 'method' => 'cash', 'reference' => 'REC-1', 'note' => 'Internal remark']);

    $this->actingAs($customer)->get("/dashboard/invoices/{$invoice->number}")->assertOk()
        ->assertSee('REC-1')
        ->assertSee('৳3,000.00')
        ->assertDontSee('Internal remark')
        ->assertDontSee('/admin/invoices');

    $this->actingAs($customer)->get("/dashboard/invoices/{$invoice->number}/print")->assertOk()
        ->assertSee(route('dashboard.invoices.show', $invoice), false)
        ->assertDontSee('/admin/invoices');
});

test('the invoice list links to details showing dates, items and amounts', function () {
    $customer = User::factory()->create();
    $invoice = customerInvoice($customer);
    $invoice->recordPayment(['amount' => 5000, 'paid_on' => today()->toDateString(), 'method' => 'cash']);

    $this->actingAs($customer)->get('/dashboard/invoices')->assertOk()
        ->assertSee(route('dashboard.invoices.show', $invoice), false);

    $this->actingAs($customer)->get("/dashboard/invoices/{$invoice->number}")->assertOk()
        ->assertSee('Invoice details')
        ->assertSee('Site survey')
        ->assertSee($invoice->due_date->format('j M Y'))
        ->assertSee('৳5,000.00')
        ->assertSee('Paid in full');
});

test('invoices carry the logo watermark for admins and customers, quotes do not', function () {
    $admin = salesAdmin();
    $customer = User::factory()->create();
    $invoice = customerInvoice($customer);

    $this->actingAs($admin)->get("/admin/invoices/{$invoice->number}")->assertOk()->assertSee('data-watermark', false);
    $this->actingAs($admin)->get("/admin/invoices/{$invoice->number}/print")->assertOk()->assertSee('data-watermark', false);
    $this->actingAs($customer)->get("/dashboard/invoices/{$invoice->number}")->assertOk()->assertSee('data-watermark', false);
    $this->actingAs($customer)->get("/dashboard/invoices/{$invoice->number}/print")->assertOk()->assertSee('data-watermark', false);

    $this->actingAs($admin)->post('/admin/quotes', documentPayload([['description' => 'Site survey', 'quantity' => 1, 'unit_price' => 5000]]));
    $this->actingAs($admin)->get('/admin/quotes/'.Quote::firstOrFail()->number.'/print')->assertOk()->assertDontSee('data-watermark', false);
});

test('the customer dashboard counts unpaid and overdue invoices', function () {
    $customer = User::factory()->create();
    customerInvoice($customer);
    customerInvoice($customer, ['issue_date' => today()->subDays(30), 'due_date' => today()->subDay()]);
    customerInvoice($customer, ['status' => 'void']);

    $this->actingAs($customer)->get('/dashboard')->assertOk()
        ->assertViewHas('stats', fn (array $stats) => $stats['unpaid'] === 2 && $stats['overdue'] === 1)
        ->assertSee('1 overdue');
});

test('a quote can be started from an enquiry', function () {
    $enquiry = Enquiry::create(['type' => 'quote', 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'organization' => 'North Clinic', 'interest' => 'x-ray-systems', 'message' => 'Quote for a mobile X-ray please.']);

    $this->actingAs(salesAdmin())->get("/admin/quotes/create?enquiry={$enquiry->id}")->assertOk()->assertSee('value="Jane Doe"', false)->assertSee('North Clinic');

    $this->actingAs(salesAdmin())->post('/admin/quotes', documentPayload([['description' => 'Mobile X-ray unit', 'quantity' => 1, 'unit_price' => 25000]], ['enquiry_id' => $enquiry->id]));

    expect(Quote::first()->enquiry_id)->toBe($enquiry->id)->and($enquiry->fresh()->status)->toBe('in_progress');
});

test('quotes move through statuses and convert to a draft invoice', function () {
    $admin = salesAdmin();
    $item = stockItem();
    $this->actingAs($admin)->post('/admin/quotes', documentPayload([['inventory_item_id' => $item->id, 'description' => 'Detector', 'quantity' => 2, 'unit_price' => 9000]]));
    $quote = Quote::firstOrFail();

    $this->actingAs($admin)->patch("/admin/quotes/{$quote->number}/status", ['status' => 'sent']);
    expect($quote->fresh()->status)->toBe('sent')->and($quote->fresh()->sent_at)->not->toBeNull();

    $this->actingAs($admin)->post("/admin/quotes/{$quote->number}/convert")->assertRedirect();

    $invoice = Invoice::firstOrFail();
    expect($invoice->quote_id)->toBe($quote->id)
        ->and($invoice->status)->toBe('draft')
        ->and((float) $invoice->total)->toBe((float) $quote->total)
        ->and($quote->fresh()->status)->toBe('invoiced')
        ->and($item->fresh()->quantity)->toBe(10); // stock is untouched until the invoice is issued

    // Invoiced quotes are locked.
    $this->actingAs($admin)->get("/admin/quotes/{$quote->number}/edit")->assertRedirect("/admin/quotes/{$quote->number}");
});

test('issuing an invoice deducts stock and voiding returns it', function () {
    $admin = salesAdmin();
    $item = stockItem(5);
    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['inventory_item_id' => $item->id, 'description' => 'Detector', 'quantity' => 3, 'unit_price' => 9000]]));
    $invoice = Invoice::firstOrFail();

    expect($invoice->number)->toBe('INV-'.now()->year.'-0001');

    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/issue")->assertSessionHasNoErrors();
    expect($invoice->fresh()->status)->toBe('issued')->and($item->fresh()->quantity)->toBe(2);

    // Issued invoices can no longer be edited.
    $this->actingAs($admin)->get("/admin/invoices/{$invoice->number}/edit")->assertRedirect("/admin/invoices/{$invoice->number}");

    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/void")->assertSessionHasNoErrors();
    expect($invoice->fresh()->status)->toBe('void')->and($item->fresh()->quantity)->toBe(5)
        ->and($item->movements()->pluck('type')->all())->toBe(['returned', 'sold', 'opening']);
});

test('an invoice cannot be issued without enough stock', function () {
    $admin = salesAdmin();
    $item = stockItem(1);
    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['inventory_item_id' => $item->id, 'description' => 'Detector', 'quantity' => 3, 'unit_price' => 9000]]));
    $invoice = Invoice::firstOrFail();

    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/issue")->assertSessionHasErrors('stock');

    expect($invoice->fresh()->status)->toBe('draft')->and($item->fresh()->quantity)->toBe(1);
});

test('payments are recorded until the invoice is paid', function () {
    $admin = salesAdmin();
    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['description' => 'Service visit', 'quantity' => 1, 'unit_price' => 1000]], ['discount' => 0, 'tax_rate' => 0]));
    $invoice = Invoice::firstOrFail();
    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/issue");

    $pay = fn (array $data) => $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/payments", $data + ['paid_on' => today()->toDateString(), 'method' => 'bank_transfer']);

    $pay(['amount' => 400])->assertSessionHasNoErrors();
    expect($invoice->fresh()->state()[0])->toBe('Partially paid')->and($invoice->fresh()->balance())->toBe(600.0);

    $pay(['amount' => 700])->assertSessionHasErrors('amount'); // more than the balance

    $pay(['amount' => 600])->assertSessionHasNoErrors();
    expect($invoice->fresh()->state()[0])->toBe('Paid')->and($invoice->fresh()->paid_at)->not->toBeNull();

    // Paid invoices cannot be voided while payments exist.
    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/void")->assertSessionHasErrors('void');

    $this->actingAs($admin)->get('/admin/invoices?status=paid')->assertSee($invoice->number);
    $this->actingAs($admin)->get("/admin/invoices/{$invoice->number}/print")->assertOk()->assertSee('PAID');
});

test('overdue invoices are reported', function () {
    $admin = salesAdmin();
    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['description' => 'Repair', 'quantity' => 1, 'unit_price' => 500]], [
        'issue_date' => today()->subDays(60)->toDateString(), 'due_date' => today()->subDays(45)->toDateString(),
    ]));
    $invoice = Invoice::firstOrFail();
    $this->actingAs($admin)->post("/admin/invoices/{$invoice->number}/issue");

    expect($invoice->fresh()->isOverdue())->toBeTrue();
    $this->actingAs($admin)->get('/admin/invoices?status=overdue')->assertSee($invoice->number);
    $this->actingAs($admin)->get('/admin/reports')->assertOk()->assertSee('31–60 days');
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('1 overdue');
});

test('invoices and inventory export as csv', function () {
    $admin = salesAdmin();
    stockItem(4, ['name' => 'Exported item']);
    $this->actingAs($admin)->post('/admin/invoices', documentPayload([['description' => 'Line', 'quantity' => 1, 'unit_price' => 10]]));

    $this->actingAs($admin)->get('/admin/invoices/export')->assertOk()->assertDownload();
    expect($this->actingAs($admin)->get('/admin/inventory/export')->streamedContent())->toContain('Exported item');
});

test('the document number sequence continues per year', function () {
    $admin = salesAdmin();
    foreach (range(1, 3) as $i) {
        $this->actingAs($admin)->post('/admin/quotes', documentPayload([['description' => "Line {$i}", 'quantity' => 1, 'unit_price' => 10]]));
    }

    expect(Quote::pluck('number')->all())->toBe(collect(range(1, 3))->map(fn ($i) => 'QT-'.now()->year.'-000'.$i)->all());
});

test('every website product has an inventory item', function () {
    expect(InventoryItem::whereNotNull('product_id')->count())->toBe(Product::count())
        ->and(InventoryItem::where('sku', 'MOBILE-X-RAY-UNIT')->first()?->name)->toBe('Mobile X-ray unit');

    $this->actingAs(salesAdmin())->get('/admin/inventory?stock=website')->assertSee('Mobile X-ray unit')->assertSee('Price not set');
});

test('adding or renaming a product keeps inventory in step', function () {
    $admin = salesAdmin();
    $category = EquipmentCategory::first();

    $this->actingAs($admin)->post('/admin/content/products', ['name' => 'Portable DR kit', 'equipment_category_id' => $category->id, 'is_active' => '1']);
    $product = Product::where('name', 'Portable DR kit')->firstOrFail();

    expect($product->inventoryItem)->not->toBeNull()
        ->and($product->inventoryItem->sku)->toBe('PORTABLE-DR-KIT')
        ->and($product->inventoryItem->quantity)->toBe(0);

    $this->actingAs($admin)->put("/admin/content/products/{$product->id}", ['name' => 'Portable DR kit (wireless)', 'slug' => $product->slug, 'equipment_category_id' => $category->id, 'is_active' => '1']);

    expect($product->inventoryItem->fresh()->name)->toBe('Portable DR kit (wireless)');
});
