<?php

namespace App\Support;

use App\Models\EquipmentCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Industry;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Throwable;

/**
 * Loads admin-managed website content from the database into the
 * config('zenray.*') arrays the site templates read. Cached until any
 * content model is saved or deleted (see Concerns\IsSiteContent).
 */
class SiteContent
{
    public const CACHE_KEY = 'zenray.content';

    public static function apply(): void
    {
        try {
            $content = Cache::rememberForever(self::CACHE_KEY, fn () => self::load());
        } catch (Throwable) {
            // Tables not migrated yet: keep the defaults from config/zenray.php.
            return;
        }

        foreach ($content as $key => $value) {
            Config::set("zenray.{$key}", $value);
        }
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private static function load(): array
    {
        $categories = EquipmentCategory::query()->active()->ordered()->get();
        $slugs = $categories->pluck('slug', 'id');

        return [
            'product_categories' => $categories->map(fn (EquipmentCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'photo' => $c->photo,
                'alt' => $c->alt ?: $c->name,
                'summary' => $c->summary,
            ])->all(),

            // Products of hidden or deleted categories are left out.
            'demo_products' => Product::query()->active()->ordered()->get()
                ->filter(fn (Product $p) => $slugs->has($p->equipment_category_id))
                ->map(fn (Product $p) => [
                    'name' => $p->name,
                    'category' => $slugs[$p->equipment_category_id],
                    'photo' => $p->photo,
                    'alt' => $p->alt ?: $p->name,
                    'summary' => $p->summary,
                    'features' => $p->features ?? [],
                    'availability' => $p->availability,
                    'example' => $p->is_example,
                ])->values()->all(),

            'services' => Service::query()->active()->ordered()->get()->map(fn (Service $s) => [
                'name' => $s->name,
                'slug' => $s->slug,
                'icon' => $s->icon,
                'summary' => $s->summary,
                'next_step' => $s->next_step,
                'action' => $s->action ?: 'Ask about '.strtolower($s->name),
            ])->all(),

            'industries' => Industry::query()->active()->ordered()->get()
                ->map(fn (Industry $i) => ['name' => $i->name, 'icon' => $i->icon, 'text' => $i->text])->all(),

            'hero_slides' => HeroSlide::query()->active()->ordered()->get()
                ->filter(fn (HeroSlide $s) => filled($s->photo))
                ->map(fn (HeroSlide $s) => [
                    'eyebrow' => $s->eyebrow,
                    'title' => $s->title,
                    'text' => $s->text,
                    'photo' => $s->photo,
                    'alt' => $s->alt ?: $s->title,
                    'category' => $s->category_slug,
                ])->values()->all(),

            'gallery' => GalleryItem::query()->active()->ordered()->get()
                ->map(fn (GalleryItem $g) => ['photo' => $g->photo, 'caption' => $g->caption, 'size' => $g->size])->all(),

            'stats' => Stat::query()->active()->ordered()->get()
                ->map(fn (Stat $s) => ['value' => $s->value, 'label' => $s->label])->all(),

            'projects' => Project::query()->active()->ordered()->get()->map(fn (Project $p) => [
                'title' => $p->title,
                'slug' => $p->slug,
                'photo' => $p->photo,
                'image_alt' => $p->alt ?: $p->title,
                'delivered' => $p->delivered,
                'location' => $p->location,
                'description' => $p->summary,
            ])->all(),
        ];
    }
}
