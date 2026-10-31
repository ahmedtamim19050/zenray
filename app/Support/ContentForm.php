<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validation and saving for admin forms built from field definitions
 * (see AdminContent). Shared by the content and blog post editors.
 */
class ContentForm
{
    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    public static function rules(array $fields, Model $model): array
    {
        $rules = [];

        foreach ($fields as $name => $field) {
            match ($field['type']) {
                'slug' => $rules[$name] = ['nullable', 'string', 'max:200', 'alpha_dash:ascii', Rule::unique($model->getTable(), $name)->ignore($model->getKey())],
                'image' => $rules += [
                    $name => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                    "{$name}_library" => ['nullable', Rule::in(array_keys(Photo::library()))],
                    "{$name}_remove" => ['nullable', 'boolean'],
                ],
                default => $rules[$name] = $field['rules'] ?? ['nullable'],
            };
        }

        return $rules;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    public static function attributes(array $fields): array
    {
        return collect($fields)->mapWithKeys(fn ($field, $name) => [$name => Str::lower($field['label'])])->all();
    }

    /**
     * Validate the request and fill the model (without saving it).
     *
     * @param  array<string, array<string, mixed>>  $fields
     */
    public static function fill(Request $request, Model $model, array $fields, string $folder): Model
    {
        $data = $request->validate(self::rules($fields, $model), [], self::attributes($fields));

        foreach ($fields as $name => $field) {
            match ($field['type']) {
                'checkbox' => $model->{$name} = $request->boolean($name),
                'lines' => $model->{$name} = collect(preg_split('/\R/', (string) ($data[$name] ?? '')))->map(fn ($line) => trim($line))->filter()->values()->all(),
                'slug' => $model->{$name} = self::slug($model, $name, $data[$name] ?? null, $data[$field['from']] ?? ''),
                'image' => self::image($request, $model, $name, $field, $folder),
                'number' => $model->{$name} = $data[$name] ?? ($name === 'sort_order' ? self::nextOrder($model) : null),
                default => $model->{$name} = $data[$name] ?? null,
            };
        }

        return $model;
    }

    /**
     * A unique slug: the one typed in, or one made from the title/name.
     */
    private static function slug(Model $model, string $column, ?string $value, string $source): string
    {
        $base = Str::slug($value ?: $source) ?: Str::lower(Str::random(8));
        $slug = $base;

        for ($i = 2; $model->newQuery()->where($column, $slug)->whereKeyNot($model->getKey())->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    /**
     * Upload a new image, pick one from the library, or remove the current one.
     *
     * @param  array<string, mixed>  $field
     */
    private static function image(Request $request, Model $model, string $name, array $field, string $folder): void
    {
        $current = $model->{$name};

        if ($request->hasFile($name)) {
            $model->{$name} = $request->file($name)->store("uploads/{$folder}", 'public');
        } elseif ($request->filled("{$name}_library")) {
            $model->{$name} = $request->input("{$name}_library");
        } elseif ($request->boolean("{$name}_remove")) {
            $model->{$name} = null;
        }

        if (($field['required'] ?? false) && blank($model->{$name})) {
            throw ValidationException::withMessages([$name => 'Please upload a photo or choose one from the library.']);
        }

        if ($current !== $model->{$name}) {
            Photo::forget($current);
        }
    }

    private static function nextOrder(Model $model): int
    {
        return $model->exists ? (int) $model->sort_order : (int) $model->newQuery()->max('sort_order') + 1;
    }
}
