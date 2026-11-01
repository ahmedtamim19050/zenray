<?php

use App\Models\EquipmentCategory;
use App\Models\GalleryItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Support\AdminContent;
use App\Support\SiteContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function admin(): User
{
    return User::factory()->admin()->create();
}

test('the original content is copied into the database', function () {
    expect(EquipmentCategory::count())->toBe(count(config('zenray.product_categories')))
        ->and(Product::count())->toBe(count(config('zenray.demo_products')))
        ->and(Service::count())->toBe(count(config('zenray.services')));
});

test('guests and customers cannot manage content', function () {
    $this->get('/admin/content/products')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get('/admin/content/products')->assertForbidden();
});

test('every content type can be listed, created and edited', function (string $resource) {
    $admin = admin();
    $model = AdminContent::find($resource)['model'];

    $this->actingAs($admin)->get("/admin/content/{$resource}")->assertOk();
    $this->actingAs($admin)->get("/admin/content/{$resource}/create")->assertOk();

    if ($item = $model::query()->first()) {
        $this->actingAs($admin)->get("/admin/content/{$resource}/{$item->id}/edit")->assertOk();
    }
})->with(array_keys(AdminContent::resources()));

test('unknown content types return 404', function () {
    $this->actingAs(admin())->get('/admin/content/nonsense')->assertNotFound();
});

test('admins can add a product with an uploaded photo and it appears on the website', function () {
    Storage::fake('public');
    $category = EquipmentCategory::first();

    $this->actingAs(admin())->post('/admin/content/products', [
        'name' => 'Ceiling-mounted DR system',
        'equipment_category_id' => $category->id,
        'summary' => 'A ceiling-mounted digital X-ray system.',
        'features' => "Wireless detector\n\nAuto positioning",
        'photo' => UploadedFile::fake()->image('dr.jpg', 800, 600),
        'is_example' => '0',
        'is_active' => '1',
    ])->assertRedirect('/admin/content/products')->assertSessionHasNoErrors();

    $product = Product::where('name', 'Ceiling-mounted DR system')->firstOrFail();
    expect($product->slug)->toBe('ceiling-mounted-dr-system')
        ->and($product->features)->toBe(['Wireless detector', 'Auto positioning'])
        ->and($product->is_example)->toBeFalse()
        ->and($product->sort_order)->toBe(Product::max('sort_order'));
    Storage::disk('public')->assertExists($product->photo);

    SiteContent::apply();
    $this->get('/products')->assertSee('Ceiling-mounted DR system')->assertSee('Wireless detector');
});

test('hidden items are not shown on the website', function () {
    Product::where('name', 'Mobile C-arm')->update(['is_active' => false]);
    SiteContent::flush();
    SiteContent::apply();

    $this->get('/')->assertDontSee('Mobile C-arm</h3>', false);
});

test('slugs are generated and kept unique', function () {
    $this->actingAs(admin())->post('/admin/content/services', ['name' => 'Equipment Installation', 'icon' => 'install', 'is_active' => '1']);

    expect(Service::where('slug', 'installation')->exists())->toBeTrue()
        ->and(Service::where('slug', 'equipment-installation')->exists())->toBeTrue();

    $this->actingAs(admin())->post('/admin/content/services', ['name' => 'Equipment Installation', 'icon' => 'install', 'is_active' => '1']);
    expect(Service::where('slug', 'equipment-installation-2')->exists())->toBeTrue();
});

test('new services become options on the enquiry form', function () {
    $this->actingAs(admin())->post('/admin/content/services', ['name' => 'Detector Repair', 'slug' => 'detector-repair', 'icon' => 'support', 'is_active' => '1']);
    SiteContent::apply();

    $this->get('/request-a-quote')->assertSee('<option value="detector-repair"', false);
});

test('content is validated', function () {
    $this->actingAs(admin())->post('/admin/content/products', ['name' => '', 'equipment_category_id' => 999, 'slug' => 'not a slug!'])
        ->assertSessionHasErrors(['name', 'equipment_category_id', 'slug']);

    $this->actingAs(admin())->post('/admin/content/gallery', ['caption' => 'No photo', 'size' => 'normal'])
        ->assertSessionHasErrors('photo');

    $this->actingAs(admin())->post('/admin/content/industries', ['name' => 'Labs', 'icon' => '<script>'])
        ->assertSessionHasErrors('icon');
});

test('a library photo can be chosen instead of uploading one', function () {
    $this->actingAs(admin())->post('/admin/content/gallery', ['caption' => 'Detector', 'size' => 'wide', 'photo_library' => 'detector-panel', 'is_active' => '1'])
        ->assertSessionHasNoErrors();

    expect(GalleryItem::latest('id')->first()->photo)->toBe('detector-panel');
});

test('items can be reordered', function () {
    [$first, $second] = Service::query()->ordered()->take(2)->get();

    $this->actingAs(admin())->post("/admin/content/services/{$second->id}/move", ['direction' => 'up'])->assertRedirect();

    expect(Service::query()->ordered()->first()->id)->toBe($second->id)
        ->and($first->fresh()->sort_order)->toBe(2);
});

test('deleting an item removes its uploaded photo', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('g.jpg')->store('uploads/gallery', 'public');
    $item = GalleryItem::create(['photo' => $path, 'caption' => 'Temp', 'size' => 'normal']);

    $this->actingAs(admin())->delete("/admin/content/gallery/{$item->id}")->assertRedirect('/admin/content/gallery');

    expect(GalleryItem::find($item->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('published projects get their own page', function () {
    $this->actingAs(admin())->post('/admin/content/projects', [
        'title' => 'City Hospital DR upgrade',
        'delivered' => 'Digital X-ray room installation',
        'summary' => 'Upgrade of a general X-ray room to digital radiography.',
        'body' => "## The brief\n\nReplace the CR workflow.",
        'photo_library' => 'room-overview',
        'is_active' => '1',
    ])->assertSessionHasNoErrors();
    SiteContent::apply();

    $this->get('/portfolio')->assertSee('City Hospital DR upgrade');
    $this->get('/portfolio/city-hospital-dr-upgrade')->assertOk()->assertSee('<h2>The brief</h2>', false);
});

test('the admin overview shows content and blog summaries', function () {
    $this->actingAs(admin())->get('/admin')->assertOk()->assertSee('Website content')->assertSee('Latest blog posts');
});

test('the gallery starts on the first large photo, or the first photo without one', function (array $sizes, int $start) {
    $items = array_map(fn (string $size) => ['photo' => 'c-arm', 'caption' => 'Photo', 'size' => $size], $sizes);

    $this->blade('<x-gallery :items="$items" />', ['items' => $items])
        ->assertSee('data-gallery-start="'.$start.'"', false);
})->with([
    'a large photo' => [['normal', 'large', 'large'], 1],
    'no large photo' => [['normal', 'wide'], 0],
]);
