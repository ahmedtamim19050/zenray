<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnquiryController extends Controller
{
    /**
     * Save a website enquiry (quote request or general question) and email it
     * to the team. The saved record is what the admin dashboard shows, so a
     * mail failure is logged but doesn't lose the enquiry.
     */
    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');

        try {
            $enquiry = Enquiry::create([...$data, 'type' => $data['type'] ?? 'contact', 'user_id' => $request->user()?->id]);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('enquiry_status', 'failed')->withFragment('enquiry');
        }

        $recipient = config('zenray.enquiries_to') ?: config('mail.from.address');

        try {
            Mail::to($recipient)->send(new EnquiryReceived([...$data, 'interest_label' => $enquiry->interestLabel()]));
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('enquiry_status', 'sent')->withFragment('enquiry');
    }
}
