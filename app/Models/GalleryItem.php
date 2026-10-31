<?php

namespace App\Models;

use App\Models\Concerns\IsSiteContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['photo', 'caption', 'size', 'sort_order', 'is_active'])]
class GalleryItem extends Model
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
