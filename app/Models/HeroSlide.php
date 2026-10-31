<?php

namespace App\Models;

use App\Models\Concerns\IsSiteContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['eyebrow', 'title', 'text', 'photo', 'alt', 'category_slug', 'sort_order', 'is_active'])]
class HeroSlide extends Model
{
    use IsSiteContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
