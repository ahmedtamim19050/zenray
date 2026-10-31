<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        return view('admin.customers.index', [
            'search' => $search,
            'customers' => User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->withCount('enquiries')
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(User $customer): View
    {
        abort_if($customer->isAdmin(), 404);

        return view('admin.customers.show', [
            'customer' => $customer,
            'enquiries' => $customer->enquiries()->latest()->get(),
            'quotes' => Quote::query()->where('user_id', $customer->id)->latest('id')->get(),
            'invoices' => Invoice::query()->where('user_id', $customer->id)->latest('id')->get(),
        ]);
    }
}
