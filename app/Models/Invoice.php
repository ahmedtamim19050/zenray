<?php

namespace App\Models;

use App\Models\Concerns\IsBillingDocument;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['number', 'quote_id', 'user_id', 'created_by', 'status', 'customer_name', 'customer_organization', 'customer_email', 'customer_phone', 'customer_address', 'issue_date', 'due_date', 'currency', 'tax_rate', 'discount', 'notes', 'terms'])]
class Invoice extends Model
{
    use IsBillingDocument;

    public const NUMBER_PREFIX_SETTING = 'invoice_prefix';

    /**
     * Stored statuses. Payment states (unpaid, partial, paid, overdue) are derived, see state().
     */
    public const STATUSES = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'void' => 'Void',
    ];

    /**
     * Filters offered in the admin list.
     */
    public const FILTERS = [
        'draft' => 'Drafts',
        'unpaid' => 'Unpaid',
        'overdue' => 'Overdue',
        'paid' => 'Paid',
        'void' => 'Void',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return $this->documentCasts() + [
            'due_date' => 'date',
            'amount_paid' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Quote, $this>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_on')->latest('id');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function balance(): float
    {
        return Money::round((float) $this->total - (float) $this->amount_paid);
    }

    public function isPaid(): bool
    {
        return $this->status === 'issued' && $this->balance() <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'issued' && ! $this->isPaid() && $this->due_date?->endOfDay()->isPast();
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function state(): array
    {
        return match (true) {
            $this->status === 'void' => ['Void', 'bg-slate-100 text-slate-500 ring-slate-200 line-through'],
            $this->status === 'draft' => ['Draft', 'bg-slate-100 text-slate-600 ring-slate-200'],
            $this->isPaid() => ['Paid', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            $this->isOverdue() => ['Overdue', 'bg-red-50 text-red-700 ring-red-200'],
            (float) $this->amount_paid > 0 => ['Partially paid', 'bg-amber-50 text-amber-700 ring-amber-200'],
            default => ['Unpaid', 'bg-sky-50 text-sky-700 ring-sky-200'],
        };
    }

    /**
     * Finalise a draft: take stocked items out of inventory and lock the invoice.
     *
     * @throws ValidationException when there is not enough stock
     */
    public function issue(?User $by = null): void
    {
        DB::transaction(function () use ($by) {
            $needed = $this->items()->whereNotNull('inventory_item_id')->get()
                ->groupBy('inventory_item_id')
                ->map(fn ($lines) => (int) ceil($lines->sum(fn ($line) => (float) $line->quantity)));

            $stock = InventoryItem::query()->whereKey($needed->keys())->lockForUpdate()->get()->keyBy('id');

            $short = $needed->filter(fn ($quantity, $id) => isset($stock[$id]) && $stock[$id]->quantity < $quantity)
                ->map(fn ($quantity, $id) => "{$stock[$id]->name}: {$quantity} needed, {$stock[$id]->quantity} in stock");

            if ($short->isNotEmpty()) {
                throw ValidationException::withMessages(['stock' => 'Not enough stock to issue this invoice. '.$short->implode('; ').'.']);
            }

            foreach ($needed as $id => $quantity) {
                $stock->get($id)?->adjust(-$quantity, 'sold', ['user_id' => $by?->id, 'invoice_id' => $this->id, 'reference' => $this->number]);
            }

            $this->update(['status' => 'issued']);
            $this->forceFill(['issued_at' => now()])->save();
        });
    }

    /**
     * Cancel an issued or draft invoice and return any sold stock.
     *
     * @throws ValidationException when payments have been recorded
     */
    public function void(?User $by = null): void
    {
        if ($this->payments()->exists()) {
            throw ValidationException::withMessages(['void' => 'Remove the recorded payments before voiding this invoice.']);
        }

        DB::transaction(function () use ($by) {
            $this->stockMovements()->where('type', 'sold')->with('item')->get()
                ->each(fn (StockMovement $movement) => $movement->item?->adjust(-$movement->quantity, 'returned', [
                    'user_id' => $by?->id, 'invoice_id' => $this->id, 'reference' => $this->number, 'note' => 'Invoice voided',
                ]));

            $this->update(['status' => 'void']);
            $this->forceFill(['voided_at' => now()])->save();
        });
    }

    /**
     * @param  array{amount: float|string, paid_on: string, method: string, reference?: string|null, note?: string|null}  $data
     */
    public function recordPayment(array $data, ?User $by = null): Payment
    {
        $payment = $this->payments()->create($data + ['recorded_by' => $by?->id]);
        $this->refreshPaid();

        return $payment;
    }

    public function refreshPaid(): void
    {
        $paid = Money::round($this->payments()->sum('amount'));

        $this->forceFill([
            'amount_paid' => $paid,
            'paid_at' => $paid >= (float) $this->total && $paid > 0 ? ($this->paid_at ?? now()) : null,
        ])->save();
    }

    /**
     * Invoices a customer may see: everything except drafts.
     *
     * @param  Builder<Invoice>  $query
     */
    public function scopeVisibleToCustomer(Builder $query): void
    {
        $query->where('status', '!=', 'draft');
    }

    /**
     * Issued invoices with money still owed.
     *
     * @param  Builder<Invoice>  $query
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->where('status', 'issued')->whereColumn('amount_paid', '<', 'total');
    }

    /**
     * @param  Builder<Invoice>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->outstanding()->whereDate('due_date', '<', today());
    }

    /**
     * @param  Builder<Invoice>  $query
     */
    public function scopeFilter(Builder $query, string $filter): void
    {
        match ($filter) {
            'draft', 'void' => $query->where('status', $filter),
            'unpaid' => $query->outstanding(),
            'overdue' => $query->overdue(),
            'paid' => $query->where('status', 'issued')->whereColumn('amount_paid', '>=', 'total'),
            default => null,
        };
    }
}
