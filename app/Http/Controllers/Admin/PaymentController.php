<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->status === 'issued', 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:'.max(0.01, $invoice->balance())],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.max' => 'The payment cannot be more than the balance due ('.Money::format($invoice->balance(), $invoice->currency).').',
        ]);

        $invoice->recordPayment($data, $request->user());

        return back()->with('status', 'Payment of '.Money::format($data['amount'], $invoice->currency).' recorded.'.($invoice->isPaid() ? ' The invoice is now paid in full.' : ''));
    }

    public function destroy(Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        $payment->delete();
        $invoice->refreshPaid();

        return back()->with('status', 'Payment removed.');
    }
}
