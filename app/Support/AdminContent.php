<?php

namespace App\Support;

use App\Models\BlogCategory;
use App\Models\EquipmentCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Industry;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\Stat;
use Illuminate\Database\Eloquent\Model;

/**
 * Registry of website content editable from the admin dashboard.
 *
 * Each resource drives the generic Admin\ContentController: list page,
 * create/edit form and validation. Field types: text, textarea, markdown,
 * number, select, icon, image, checkbox, lines (one item per line), slug,
 * date. A "slug" field is generated from the "from" field when left empty.
 */
class AdminContent
{
    public const ICONS = ['xray', 'detector', 'accessories', 'monitor', 'install', 'support', 'inspect', 'consult', 'shield', 'users', 'layers', 'folder', 'check', 'chat', 'document', 'mail', 'phone', 'map-pin', 'clock', 'user'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function resources(): array
    {
        $order = ['label' => 'Display order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999'], 'help' => 'Lower numbers appear first. Leave empty to add at the end.'];
        $active = ['label' => 'Show on website', 'type' => 'checkbox', 'rules' => ['boolean'], 'default' => true];
        $alt = ['label' => 'Image description (alt text)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Describe the photo for people using screen readers.'];

        return [
            'hero-slides' => [
                'model' => HeroSlide::class,
                'group' => 'Homepage',
                'singular' => 'Hero slide',
                'plural' => 'Hero slides',
                'icon' => 'monitor',
                'description' => 'The X-ray viewer carousel at the top of the homepage.',
                'list' => ['image' => 'photo', 'title' => 'title', 'subtitle' => 'eyebrow'],
                'fields' => [
                    'eyebrow' => ['label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:80'], 'help' => 'Short category label, e.g. "Digital Radiography".'],
                    'title' => ['label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                    'text' => ['label' => 'Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300']],
                    'category_slug' => ['label' => 'Linked equipment category', 'type' => 'select', 'options' => fn () => self::categoryOptions('slug'), 'rules' => ['nullable', 'string', 'max:140'], 'help' => 'Buttons on the slide filter the equipment list and pre-fill the quote form.'],
                    'photo' => ['label' => 'Photo', 'type' => 'image', 'required' => true],
                    'alt' => $alt,
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'stats' => [
                'model' => Stat::class,
                'group' => 'Homepage',
                'singular' => 'Figure',
                'plural' => 'Company figures',
                'icon' => 'check',
                'description' => 'The numbers band on the homepage. It is hidden while there are no figures. Only add verified numbers.',
                'list' => ['title' => 'value', 'subtitle' => 'label'],
                'fields' => [
                    'value' => ['label' => 'Value', 'type' => 'text', 'rules' => ['required', 'string', 'max:40'], 'help' => 'For example: 10+'],
                    'label' => ['label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:120'], 'help' => 'For example: Years of experience'],
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'industries' => [
                'model' => Industry::class,
                'group' => 'Homepage',
                'singular' => 'Facility type',
                'plural' => 'Who we serve',
                'icon' => 'users',
                'description' => 'Facility types shown in the "Who we serve" section.',
                'list' => ['icon' => 'icon', 'title' => 'name', 'subtitle' => 'text'],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                    'icon' => ['label' => 'Icon', 'type' => 'icon', 'rules' => ['required', 'in:'.implode(',', self::ICONS)], 'default' => 'layers'],
                    'text' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300']],
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'equipment-categories' => [
                'model' => EquipmentCategory::class,
                'group' => 'Equipment',
                'singular' => 'Category',
                'plural' => 'Equipment categories',
                'icon' => 'folder',
                'description' => 'Categories used for the equipment page, menus, filters and the quote form.',
                'list' => ['image' => 'photo', 'title' => 'name', 'subtitle' => 'summary', 'count' => ['products', 'product']],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                    'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'name', 'help' => 'Used in links and the quote form. Leave empty to create it from the name.'],
                    'summary' => ['label' => 'Summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:400']],
                    'photo' => ['label' => 'Photo', 'type' => 'image'],
                    'alt' => $alt,
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'products' => [
                'model' => Product::class,
                'group' => 'Equipment',
                'singular' => 'Product',
                'plural' => 'Products',
                'icon' => 'xray',
                'description' => 'Equipment cards on the homepage and equipment page. Each product has a matching item in Inventory for stock and prices.',
                'with' => ['category'],
                'list' => ['image' => 'photo', 'title' => 'name', 'subtitle' => 'category.name'],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:160']],
                    'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'name'],
                    'equipment_category_id' => ['label' => 'Category', 'type' => 'select', 'options' => fn () => self::categoryOptions('id'), 'rules' => ['required', 'exists:equipment_categories,id']],
                    'summary' => ['label' => 'Summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
                    'features' => ['label' => 'Key features', 'type' => 'lines', 'rules' => ['nullable', 'string', 'max:2000'], 'help' => 'One feature per line. Up to three are shown on the card. Only add verified details.'],
                    'availability' => ['label' => 'Availability note', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'If empty: "Availability and pricing on request."'],
                    'photo' => ['label' => 'Photo', 'type' => 'image'],
                    'alt' => $alt,
                    'is_example' => ['label' => 'Mark as "Example listing"', 'type' => 'checkbox', 'rules' => ['boolean'], 'default' => true, 'help' => 'Untick for real products you have in stock or can supply.'],
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'services' => [
                'model' => Service::class,
                'group' => 'Services & projects',
                'singular' => 'Service',
                'plural' => 'Services',
                'icon' => 'support',
                'description' => 'Services on the homepage, services page, menus and quote form.',
                'list' => ['icon' => 'icon', 'title' => 'name', 'subtitle' => 'summary'],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                    'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'name'],
                    'icon' => ['label' => 'Icon', 'type' => 'icon', 'rules' => ['required', 'in:'.implode(',', self::ICONS)], 'default' => 'support'],
                    'summary' => ['label' => 'Summary', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:400']],
                    'next_step' => ['label' => 'Next step', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'What the customer should do or share to get started.'],
                    'action' => ['label' => 'Button text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:80'], 'help' => 'For example: Ask about installation'],
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'projects' => [
                'model' => Project::class,
                'group' => 'Services & projects',
                'singular' => 'Project',
                'plural' => 'Projects',
                'icon' => 'layers',
                'description' => 'Case studies on the projects page. Only publish projects approved by the facility involved.',
                'list' => ['image' => 'photo', 'title' => 'title', 'subtitle' => 'delivered'],
                'fields' => [
                    'title' => ['label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:160']],
                    'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'title'],
                    'delivered' => ['label' => 'What was delivered', 'type' => 'text', 'rules' => ['required', 'string', 'max:160'], 'help' => 'For example: Digital X-ray room installation'],
                    'location' => ['label' => 'Location', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:160']],
                    'completed_on' => ['label' => 'Completed on', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    'summary' => ['label' => 'Summary', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
                    'body' => ['label' => 'Full story', 'type' => 'markdown', 'rules' => ['nullable', 'string', 'max:50000']],
                    'photo' => ['label' => 'Photo', 'type' => 'image', 'required' => true],
                    'alt' => $alt,
                    'sort_order' => $order,
                    'is_active' => ['label' => 'Published', 'type' => 'checkbox', 'rules' => ['boolean'], 'default' => false],
                ],
            ],
            'gallery' => [
                'model' => GalleryItem::class,
                'group' => 'Services & projects',
                'singular' => 'Gallery photo',
                'plural' => 'Gallery',
                'icon' => 'expand',
                'description' => 'Photos in the gallery on the homepage and projects page.',
                'list' => ['image' => 'photo', 'title' => 'caption', 'subtitle' => 'size'],
                'fields' => [
                    'photo' => ['label' => 'Photo', 'type' => 'image', 'required' => true],
                    'caption' => ['label' => 'Caption', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'help' => 'Also used as the image description.'],
                    'size' => ['label' => 'Tile size', 'type' => 'select', 'options' => fn () => ['normal' => 'Normal', 'wide' => 'Wide', 'tall' => 'Tall', 'large' => 'Large'], 'rules' => ['required', 'in:normal,wide,tall,large'], 'default' => 'normal'],
                    'sort_order' => $order,
                    'is_active' => $active,
                ],
            ],
            'blog-categories' => [
                'model' => BlogCategory::class,
                'group' => 'Blog',
                'singular' => 'Blog category',
                'plural' => 'Blog categories',
                'icon' => 'folder',
                'description' => 'Topics used to group blog posts.',
                'list' => ['title' => 'name', 'subtitle' => 'description', 'count' => ['posts', 'post']],
                'fields' => [
                    'name' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:80']],
                    'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'name'],
                    'description' => ['label' => 'Description', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'sort_order' => $order,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $key): ?array
    {
        return self::resources()[$key] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(string $key): array
    {
        return self::find($key)['fields'] ?? [];
    }

    /**
     * Read a dotted list attribute ("category.name") from a model.
     */
    public static function display(Model $model, ?string $path): ?string
    {
        return $path ? (string) data_get($model, $path) : null;
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(string $key): array
    {
        return EquipmentCategory::query()->ordered()->pluck('name', $key)->all();
    }
}
