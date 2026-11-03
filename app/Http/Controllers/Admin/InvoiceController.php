<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesDocuments;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDocumentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use ManagesDocuments;

    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $invoices = Invoice::query()
            ->when(array_key_exists($filter, Invoice::FILTERS), fn ($q) => $q->filter($filter))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'filter' => $filter,
            'search' => $search,
            'counts' => collect(Invoice::FILTERS)->map(fn ($label, $key) => Invoice::query()->filter($key)->count())->put('', Invoice::query()->count()),
            'outstanding' => Invoice::query()->outstanding()->selectRaw('sum(total - amount_paid) as owed')->value('owed') ?? 0,
            'overdue' => Invoice::query()->overdue()->selectRaw('sum(total - amount_paid) as owed')->value('owed') ?? 0,
            'paidThisMonth' => Payment::query()->whereBetween('paid_on', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
        ]);
    }

    public function create(Request $request): View
    {
        $invoice = new Invoice($this->documentDefaults() + [
            'due_date' => today()->addDays((int) config('zenray.billing.payment_terms_days')),
            'notes' => config('zenray.billing.invoice_notes'),
        ]);

        if ($customer = User::query()->where('role', User::ROLE_CUSTOMER)->find($request->integer('customer'))) {
            $invoice->fill($this->customerFields($customer));
        }

        return view('admin.invoices.form', ['invoice' => $invoice] + $this->editorData($invoice));
    }

    public function store(SaveDocumentRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = Invoice::create($request->document() + [
                'number' => Invoice::nextNumber(),
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);
            $invoice->syncItems($request->validated('items'));

            return $invoice;
        });

        return to_route('admin.invoices.show', $invoice)->with('status', "Draft invoice {$invoice->number} created. Issue it when it is ready to send.");
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load(['items.inventoryItem', 'payments.recorder', 'quote', 'customer', 'creator']),
        ]);
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if (! $invoice->isEditable()) {
            return to_route('admin.invoices.show', $invoice)->withErrors(['invoice' => 'Only draft invoices can be edited. Void it and create a new one if something needs to change.']);
        }

        return view('admin.invoices.form', ['invoice' => $invoice] + $this->editorData($invoice->load('items')));
    }

    public function update(SaveDocumentRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->isEditable(), 403);

        DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->document());
            $invoice->syncItems($request->validated('items'));
        });

        return to_route('admin.invoices.show', $invoice)->with('status', 'Invoice saved.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (! $invoice->isEditable()) {
            return back()->withErrors(['invoice' => 'Only draft invoices can be deleted. Void issued invoices instead.']);
        }

        $invoice->items()->delete();
        $invoice->delete();
        $invoice->quote?->update(['status' => 'accepted']);

        return to_route('admin.invoices.index')->with('status', "Draft invoice {$invoice->number} deleted.");
    }

    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->status === 'draft', 403);

        $invoice->issue($request->user());

        return back()->with('status', "Invoice {$invoice->number} issued. Stocked items have been taken out of inventory.");
    }

    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'void', 403);

        $invoice->void($request->user());

        return back()->with('status', "Invoice {$invoice->number} voided. Any sold stock has been returned to inventory.");
    }

    public function print(Request $request, Invoice $invoice): View
    {
        return view('admin.documents.print', [
            'document' => $invoice->load(['items', 'payments']),
            'type' => 'invoice',
            'padToggle' => true,
            'onPad' => $request->boolean('pad'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filter = $request->string('status')->toString();

        $invoices = Invoice::query()
            ->when(array_key_exists($filter, Invoice::FILTERS), fn ($q) => $q->filter($filter))
            ->oldest('issue_date')
            ->get();

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Number', 'Status', 'Customer', 'Organization', 'Email', 'Issue date', 'Due date', 'Currency', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Balance']);

            foreach ($invoices as $invoice) {
                fputcsv($out, [
                    $invoice->number, $invoice->state()[0], $invoice->customer_name, $invoice->customer_organization, $invoice->customer_email,
                    $invoice->issue_date->toDateString(), $invoice->due_date?->toDateString(), $invoice->currency,
                    $invoice->subtotal, $invoice->discount, $invoice->tax_total, $invoice->total, $invoice->amount_paid, number_format($invoice->balance(), 2, '.', ''),
                ]);
            }

            fclose($out);
        }, 'invoices-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
