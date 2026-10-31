<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_item_id', 'user_id', 'invoice_id', 'type', 'quantity', 'balance', 'unit_cost', 'reference', 'note'])]
class StockMovement extends Model
{
    /**
     * Type => [label, badge classes].
     */
    public const TYPES = [
        'opening' => ['Opening stock', 'bg-slate-100 text-slate-700 ring-slate-200'],
        'received' => ['Received', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'sold' => ['Sold', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'returned' => ['Returned', 'bg-violet-50 text-violet-700 ring-violet-200'],
        'removed' => ['Removed', 'bg-red-50 text-red-700 ring-red-200'],
        'counted' => ['Stock count', 'bg-signal-50 text-signal-700 ring-signal-200'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2'];
    }

    /**
     * @return BelongsTo<InventoryItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type][0] ?? ucfirst($this->type);
    }

    public function typeClasses(): string
    {
        return self::TYPES[$this->type][1] ?? self::TYPES['opening'][1];
    }
}
