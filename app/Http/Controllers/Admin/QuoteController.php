<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesDocuments;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDocumentRequest;
use App\Models\Enquiry;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    use ManagesDocuments;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $quotes = Quote::query()
            ->with('invoice')
            ->when($status === 'expired', fn ($q) => $q->whereIn('status', ['draft', 'sent'])->whereDate('valid_until', '<', today()))
            ->when(array_key_exists($status, Quote::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'status' => $status,
            'search' => $search,
            'counts' => Quote::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'openValue' => Quote::query()->whereIn('status', ['sent', 'accepted'])->sum('total'),
        ]);
    }

    public function create(Request $request): View
    {
        $quote = new Quote($this->documentDefaults() + [
            'valid_until' => today()->addDays((int) config('zenray.billing.quote_valid_days')),
            'terms' => config('zenray.billing.quote_terms'),
        ]);

        // Start from an enquiry or a customer account when linked from those pages.
        if ($enquiry = Enquiry::find($request->integer('enquiry'))) {
            $quote->fill([
                'enquiry_id' => $enquiry->id,
                'user_id' => $enquiry->user_id,
                'customer_name' => $enquiry->name,
                'customer_organization' => $enquiry->organization,
                'customer_email' => $enquiry->email,
                'customer_phone' => $enquiry->phone,
            ]);
        } elseif ($customer = User::query()->where('role', User::ROLE_CUSTOMER)->find($request->integer('customer'))) {
            $quote->fill($this->customerFields($customer));
        }

        return view('admin.quotes.form', ['quote' => $quote] + $this->editorData($quote));
    }

    public function store(SaveDocumentRequest $request): RedirectResponse
    {
        $quote = DB::transaction(function () use ($request) {
            $quote = Quote::create($request->document() + [
                'number' => Quote::nextNumber(),
                'status' => 'draft',
                'created_by' => $request->user()->id,
                'enquiry_id' => Enquiry::find($request->integer('enquiry_id'))?->id,
            ]);
            $quote->syncItems($request->validated('items'));

            return $quote;
        });

        // Working on a quote moves a new enquiry along.
        if ($quote->enquiry?->status === 'new') {
            $quote->enquiry->update(['status' => 'in_progress']);
        }

        return to_route('admin.quotes.show', $quote)->with('status', "Quote {$quote->number} created.");
    }

    public function show(Quote $quote): View
    {
        return view('admin.quotes.show', ['quote' => $quote->load(['items', 'invoice', 'enquiry', 'customer', 'creator'])]);
    }

    public function edit(Quote $quote): View|RedirectResponse
    {
        if (! $quote->isEditable()) {
            return to_route('admin.quotes.show', $quote)->withErrors(['quote' => 'Invoiced quotes can no longer be edited.']);
        }

        return view('admin.quotes.form', ['quote' => $quote] + $this->editorData($quote->load('items')));
    }

    public function update(SaveDocumentRequest $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->isEditable(), 403);

        DB::transaction(function () use ($request, $quote) {
            $quote->update($request->document());
            $quote->syncItems($request->validated('items'));
        });

        return to_route('admin.quotes.show', $quote)->with('status', 'Quote saved.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        if ($quote->invoice()->exists()) {
            return back()->withErrors(['quote' => 'This quote has an invoice and cannot be deleted.']);
        }

        $quote->items()->delete();
        $quote->delete();

        return to_route('admin.quotes.index')->with('status', "Quote {$quote->number} deleted.");
    }

    public function status(Request $request, Quote $quote): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'declined'])]])['status'];
        abort_unless($quote->isEditable(), 403);

        $quote->update(['status' => $status]);
        if ($status === 'sent') {
            $quote->forceFill(['sent_at' => now()])->save();
        }

        if ($status === 'accepted' && $quote->enquiry && $quote->enquiry->status !== 'closed') {
            $quote->enquiry->update(['status' => 'quoted']);
        }

        return back()->with('status', 'Quote marked as '.strtolower(Quote::STATUSES[$status][0]).'.');
    }

    public function convert(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->invoice) {
            return to_route('admin.invoices.show', $quote->invoice);
        }

        $invoice = $quote->convertToInvoice($request->user());

        return to_route('admin.invoices.show', $invoice)->with('status', "Draft invoice {$invoice->number} created from quote {$quote->number}. Check it, then issue it.");
    }

    public function duplicate(Request $request, Quote $quote): RedirectResponse
    {
        $copy = DB::transaction(function () use ($request, $quote) {
            $copy = $quote->replicate(['number', 'status', 'sent_at', 'subtotal', 'tax_total', 'total']);
            $copy->fill([
                'number' => Quote::nextNumber(),
                'status' => 'draft',
                'issue_date' => today(),
                'valid_until' => today()->addDays((int) config('zenray.billing.quote_valid_days')),
                'created_by' => $request->user()->id,
            ])->save();
            $copy->syncItems($quote->items->map->only(['inventory_item_id', 'description', 'quantity', 'unit', 'unit_price'])->all());

            return $copy;
        });

        return to_route('admin.quotes.edit', $copy)->with('status', "Copied to new quote {$copy->number}.");
    }

    public function print(Quote $quote): View
    {
        return view('admin.documents.print', ['document' => $quote->load('items'), 'type' => 'quote']);
    }
}
