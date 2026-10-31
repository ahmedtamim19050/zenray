<?php

namespace App\Models;

use App\Models\Concerns\IsSiteContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'delivered', 'location', 'photo', 'alt', 'summary', 'body', 'completed_on', 'sort_order', 'is_active'])]
class Project extends Model
{
    use IsSiteContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['completed_on' => 'date', 'is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
