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

        return view('customer.dashboard', [
            'user' => $user,
            'stats' => [
                'total' => (clone $enquiries)->count(),
                'open' => (clone $enquiries)->open()->count(),
                'quoted' => (clone $enquiries)->where('status', 'quoted')->count(),
            ],
            'recent' => (clone $enquiries)->latest()->limit(5)->get(),
        ]);
    }
}
