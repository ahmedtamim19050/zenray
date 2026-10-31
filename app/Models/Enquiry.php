<?php

namespace App\Models;

use App\Http\Requests\StoreEnquiryRequest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'name', 'organization', 'email', 'phone', 'interest', 'message', 'status', 'admin_note'])]
class Enquiry extends Model
{
    /**
     * Status => [label, badge classes].
     */
    public const STATUSES = [
        'new' => ['New', 'bg-signal-100 text-signal-700 ring-signal-200'],
        'in_progress' => ['In progress', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'quoted' => ['Quote sent', 'bg-violet-50 text-violet-700 ring-violet-200'],
        'closed' => ['Closed', 'bg-slate-100 text-slate-600 ring-slate-200'],
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst($this->status);
    }

    public function statusClasses(): string
    {
        return self::STATUSES[$this->status][1] ?? self::STATUSES['closed'][1];
    }

    public function interestLabel(): string
    {
        return StoreEnquiryRequest::interestOptions()[$this->interest] ?? $this->interest;
    }

    public function typeLabel(): string
    {
        return $this->type === 'quote' ? 'Quote request' : 'General enquiry';
    }

    /**
     * @param  Builder<Enquiry>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', '!=', 'closed');
    }
}
