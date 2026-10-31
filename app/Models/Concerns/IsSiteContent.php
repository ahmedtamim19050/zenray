<?php

namespace App\Models\Concerns;

use App\Support\SiteContent;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared behaviour for admin-managed website content: ordering, an
 * "active" filter, and refreshing the cached site content on change.
 */
trait IsSiteContent
{
    public static function bootIsSiteContent(): void
    {
        static::saved(fn () => SiteContent::flush());
        static::deleted(fn () => SiteContent::flush());
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
