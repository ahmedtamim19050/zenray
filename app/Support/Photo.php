<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * A content photo is either a key from the built-in library in
 * config('zenray.photos') (responsive WebP files in public/images/photos)
 * or the path of an image uploaded from the admin (public disk).
 */
class Photo
{
    public static function isLibrary(?string $photo): bool
    {
        return filled($photo) && is_array(config("zenray.photos.{$photo}"));
    }

    /**
     * URL of the largest available version.
     */
    public static function url(?string $photo): ?string
    {
        if (blank($photo)) {
            return null;
        }

        if (self::isLibrary($photo)) {
            return asset("images/photos/{$photo}-".last(config("zenray.photos.{$photo}.widths")).'.webp');
        }

        return Storage::disk('public')->url($photo);
    }

    /**
     * Library photos as [key => label] for admin pickers.
     *
     * @return array<string, string>
     */
    public static function library(): array
    {
        return collect(config('zenray.photos'))->keys()
            ->mapWithKeys(fn ($key) => [$key => str($key)->replace('-', ' ')->ucfirst()->toString()])
            ->all();
    }

    /**
     * Delete an uploaded file (library photos are left alone).
     */
    public static function forget(?string $photo): void
    {
        if (filled($photo) && ! self::isLibrary($photo)) {
            Storage::disk('public')->delete($photo);
        }
    }
}
