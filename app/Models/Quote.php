<?php

namespace App\Models;

use App\Models\Concerns\IsBillingDocument;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable(['number', 'user_id', 'enquiry_id', 'created_by', 'status', 'customer_name', 'customer_organization', 'customer_email', 'customer_phone', 'customer_address', 'issue_date', 'valid_until', 'currency', 'tax_rate', 'discount', 'notes', 'terms'])]
class Quote extends Model
{
    use IsBillingDocument;

    public const NUMBER_PREFIX_SETTING = 'quote_prefix';

    /**
     * Status => [label, badge classes]. "Expired" is derived, see state().
     */
    public const STATUSES = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-600 ring-slate-200'],
        'sent' => ['Sent', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'accepted' => ['Accepted', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'declined' => ['Declined', 'bg-red-50 text-red-700 ring-red-200'],
        'invoiced' => ['Invoiced', 'bg-violet-50 text-violet-700 ring-violet-200'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return $this->documentCasts() + [
            'valid_until' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Enquiry, $this>
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * @return HasOne<Invoice, $this>
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['draft', 'sent'], true) && $this->valid_until?->endOfDay()->isPast();
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function state(): array
    {
        return $this->isExpired()
            ? ['Expired', 'bg-amber-50 text-amber-700 ring-amber-200']
            : (self::STATUSES[$this->status] ?? self::STATUSES['draft']);
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, ['invoiced'], true);
    }

    /**
     * Create a draft invoice with this quote's customer and line items.
     */
    public function convertToInvoice(?User $by = null): Invoice
    {
        return DB::transaction(function () use ($by) {
            $invoice = Invoice::create([
                'number' => Invoice::nextNumber(),
                'quote_id' => $this->id,
                'user_id' => $this->user_id,
                'created_by' => $by?->id,
                'status' => 'draft',
                'issue_date' => today(),
                'due_date' => today()->addDays((int) config('zenray.billing.payment_terms_days')),
                'currency' => $this->currency,
                'tax_rate' => $this->tax_rate,
                'discount' => $this->discount,
                'notes' => config('zenray.billing.invoice_notes'),
                'terms' => $this->terms,
            ] + $this->only(self::CUSTOMER_FIELDS));

            $invoice->syncItems($this->items->map->only(['inventory_item_id', 'description', 'quantity', 'unit', 'unit_price'])->all());

            $this->update(['status' => 'invoiced']);

            return $invoice;
        });
    }
}
