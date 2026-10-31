<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Site settings editable by admins. Keys mirror paths under config('zenray'),
 * e.g. "contact.email" overrides config('zenray.contact.email').
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const CACHE_KEY = 'zenray.settings';

    /**
     * All stored settings as [key => value], cached until the next save.
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    /**
     * Save several settings at once and refresh the cache.
     *
     * @param  array<string, string|null>  $values
     */
    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => filled($value) ? $value : null]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
