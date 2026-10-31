<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'blog_category_id', 'title', 'slug', 'excerpt', 'body', 'photo', 'alt', 'status', 'published_at', 'is_featured', 'meta_description'])]
class Post extends Model
{
    public const STATUSES = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-600 ring-slate-200'],
        'published' => ['Published', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<BlogCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /**
     * Posts visible on the website: published, with a publish date that has arrived.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function isLive(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }

    /**
     * Admin-facing state: draft, scheduled (published in the future) or published.
     *
     * @return array{0: string, 1: string}
     */
    public function state(): array
    {
        if ($this->status === 'published' && $this->published_at?->isFuture()) {
            return ['Scheduled', 'bg-sky-50 text-sky-700 ring-sky-200'];
        }

        return self::STATUSES[$this->status] ?? self::STATUSES['draft'];
    }

    /**
     * Body rendered from Markdown. Raw HTML is stripped and unsafe links removed.
     */
    public function bodyHtml(): string
    {
        return Str::markdown($this->body ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function summary(int $limit = 180): string
    {
        return $this->excerpt ?: Str::limit(trim(strip_tags($this->bodyHtml())), $limit);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body ?? '')) / 220));
    }
}
