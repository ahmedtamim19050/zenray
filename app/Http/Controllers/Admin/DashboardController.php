<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Quote;
use App\Models\User;
use App\Support\AdminContent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $byStatus = Enquiry::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $options = StoreEnquiryRequest::interestOptions();
        $byInterest = Enquiry::query()->selectRaw('interest, count(*) as total')->groupBy('interest')->orderByDesc('total')->pluck('total', 'interest')
            ->mapWithKeys(fn ($total, $interest) => [$options[$interest] ?? $interest => $total]);

        return view('admin.dashboard', [
            'stats' => [
                'enquiries' => $byStatus->sum(),
                'new' => $byStatus['new'] ?? 0,
                'open' => $byStatus->except('closed')->sum(),
                'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
                'last7' => Enquiry::query()->where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'byStatus' => $byStatus,
            'byInterest' => $byInterest,
            'recent' => Enquiry::query()->with('user')->latest()->limit(6)->get(),
            'newCustomers' => User::query()->where('role', User::ROLE_CUSTOMER)->latest()->limit(5)->get(),
            'sales' => [
                'received_month' => Payment::query()->whereBetween('paid_on', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
                'outstanding' => Invoice::query()->outstanding()->selectRaw('sum(total - amount_paid) as owed')->value('owed') ?? 0,
                'overdue_count' => Invoice::query()->overdue()->count(),
                'open_quotes' => Quote::query()->whereIn('status', ['sent', 'accepted'])->count(),
                'open_quotes_value' => Quote::query()->whereIn('status', ['sent', 'accepted'])->sum('total'),
                'low_stock' => InventoryItem::query()->where('is_active', true)->lowStock()->count(),
            ],
            'recentInvoices' => Invoice::query()->whereIn('status', ['issued', 'draft'])->latest('id')->limit(5)->get(),
            'posts' => Post::query()->with('category')->latest('updated_at')->limit(5)->get(),
            // [label, icon, visible count, total count] per content type
            'content' => collect(AdminContent::resources())->map(function (array $resource) {
                $query = $resource['model']::query();
                $total = $query->count();
                $hasActive = array_key_exists('is_active', $resource['fields']);

                return [$resource['plural'], $resource['icon'], $hasActive ? (clone $query)->where('is_active', true)->count() : $total, $total];
            }),
        ]);
    }
}
