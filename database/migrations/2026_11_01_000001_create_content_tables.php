<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Website content managed from the admin dashboard. The tables start out
 * with the display content that previously lived in config/zenray.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('photo')->nullable();
            $table->string('alt')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('photo')->nullable();
            $table->string('alt')->nullable();
            $table->text('summary')->nullable();
            $table->json('features')->nullable();
            $table->string('availability')->nullable();
            $table->boolean('is_example')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('icon', 40)->default('support');
            $table->text('summary')->nullable();
            $table->string('next_step')->nullable();
            $table->string('action', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('icon', 40)->default('layers');
            $table->text('text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 80);
            $table->string('title', 120);
            $table->text('text')->nullable();
            $table->string('photo')->nullable();
            $table->string('alt')->nullable();
            $table->string('category_slug', 140)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('photo');
            $table->string('caption');
            $table->string('size', 20)->default('normal');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('value', 40);
            $table->string('label', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('delivered', 160);
            $table->string('location', 160)->nullable();
            $table->string('photo')->nullable();
            $table->string('alt')->nullable();
            $table->text('summary');
            $table->longText('body')->nullable();
            $table->date('completed_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedFromConfig();
    }

    public function down(): void
    {
        foreach (['projects', 'stats', 'gallery_items', 'hero_slides', 'industries', 'services', 'products', 'equipment_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Copy the original display content from config/zenray.php.
     */
    private function seedFromConfig(): void
    {
        $now = now();
        $rows = fn (array $items, callable $map) => collect($items)->values()
            ->map(fn ($item, $i) => $map($item) + ['sort_order' => $i + 1, 'created_at' => $now, 'updated_at' => $now])
            ->all();

        DB::table('equipment_categories')->insert($rows(config('zenray.product_categories', []), fn ($c) => [
            'name' => $c['name'], 'slug' => $c['slug'], 'photo' => $c['photo'], 'alt' => $c['alt'], 'summary' => $c['summary'],
        ]));

        $categoryIds = DB::table('equipment_categories')->pluck('id', 'slug');

        DB::table('products')->insert($rows(config('zenray.demo_products', []), fn ($p) => [
            'equipment_category_id' => $categoryIds[$p['category']] ?? null,
            'name' => $p['name'], 'slug' => Str::slug($p['name']), 'photo' => $p['photo'], 'alt' => $p['alt'], 'summary' => $p['summary'],
        ]));

        DB::table('services')->insert($rows(config('zenray.services', []), fn ($s) => [
            'name' => $s['name'], 'slug' => $s['slug'], 'icon' => $s['icon'], 'summary' => $s['summary'], 'next_step' => $s['next_step'], 'action' => $s['action'],
        ]));

        DB::table('industries')->insert($rows(config('zenray.industries', []), fn ($i) => [
            'name' => $i['name'], 'icon' => $i['icon'], 'text' => $i['text'],
        ]));

        DB::table('hero_slides')->insert($rows(config('zenray.hero_slides', []), fn ($s) => [
            'eyebrow' => $s['eyebrow'], 'title' => $s['title'], 'text' => $s['text'], 'photo' => $s['photo'], 'alt' => $s['alt'], 'category_slug' => $s['category'],
        ]));

        DB::table('gallery_items')->insert($rows(config('zenray.gallery', []), fn ($g) => [
            'photo' => $g['photo'], 'caption' => $g['caption'], 'size' => $g['size'],
        ]));

        DB::table('stats')->insert($rows(config('zenray.stats', []), fn ($s) => [
            'value' => $s['value'], 'label' => $s['label'],
        ]));
    }
};
