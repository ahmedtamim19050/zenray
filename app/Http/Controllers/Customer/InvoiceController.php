<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.invoices.index', [
            'invoices' => $request->user()->invoices()->visibleToCustomer()->latest('issue_date')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, string $number): View
    {
        return view('customer.invoices.show', ['invoice' => $this->ownInvoice($request, $number)->load(['items', 'payments', 'quote'])]);
    }

    public function print(Request $request, string $number): View
    {
        $invoice = $this->ownInvoice($request, $number)->load(['items', 'payments', 'quote']);

        return view('admin.documents.print', [
            'document' => $invoice,
            'type' => 'invoice',
            'backUrl' => route('dashboard.invoices.show', $invoice),
        ]);
    }

    /**
     * Customers may only see their own invoices, and never drafts.
     */
    private function ownInvoice(Request $request, string $number): Invoice
    {
        return $request->user()->invoices()->visibleToCustomer()->where('number', $number)->firstOrFail();
    }
}
