<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $enquiries = $user->enquiries();
        $invoices = $user->invoices()->visibleToCustomer();

        return view('customer.dashboard', [
            'user' => $user,
            'stats' => [
                'total' => (clone $enquiries)->count(),
                'open' => (clone $enquiries)->open()->count(),
                'quoted' => (clone $enquiries)->where('status', 'quoted')->count(),
                'unpaid' => (clone $invoices)->outstanding()->count(),
                'overdue' => (clone $invoices)->overdue()->count(),
            ],
            'recent' => (clone $enquiries)->latest()->limit(5)->get(),
            'recentInvoices' => (clone $invoices)->latest('issue_date')->latest('id')->limit(5)->get(),
        ]);
    }
}
