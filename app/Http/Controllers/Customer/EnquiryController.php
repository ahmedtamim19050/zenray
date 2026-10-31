<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.enquiries.index', [
            'enquiries' => $request->user()->enquiries()->latest()->paginate(10),
        ]);
    }

    public function show(Request $request, Enquiry $enquiry): View
    {
        // Customers may only see their own enquiries.
        abort_unless($enquiry->user_id === $request->user()->id, 404);

        return view('customer.enquiries.show', ['enquiry' => $enquiry]);
    }

    public function create(): View
    {
        return view('customer.enquiries.create');
    }
}
