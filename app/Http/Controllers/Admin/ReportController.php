<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\LineItem;
use App\Models\Payment;
use App\Models\Quote;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Sales, receivables, quotation and inventory reports. Monthly figures are
 * grouped in PHP so the queries stay portable across database engines.
 */
class ReportController extends Controller
{
    public function __invoke(): View
    {
        $start = now()->startOfMonth()->subMonths(11);
        $months = collect(range(0, 11))->mapWithKeys(fn ($i) => [$start->copy()->addMonths($i)->format('Y-m') => 0.0]);

        $received = Payment::query()->where('paid_on', '>=', $start)->get(['amount', 'paid_on'])
            ->groupBy(fn (Payment $p) => $p->paid_on->format('Y-m'))->map(fn ($group) => Money::round($group->sum('amount')));

        $invoiced = Invoice::query()->where('status', 'issued')->where('issue_date', '>=', $start)->get(['total', 'issue_date'])
            ->groupBy(fn (Invoice $i) => $i->issue_date->format('Y-m'))->map(fn ($group) => Money::round($group->sum('total')));

        $monthly = $months->map(fn ($_, $month) => [
            // Parse with day 01: "Y-m" alone takes today's day and overflows on the 31st.
            'label' => Carbon::createFromFormat('Y-m-d', "{$month}-01")->format('M y'),
            'received' => $received[$month] ?? 0.0,
            'invoiced' => $invoiced[$month] ?? 0.0,
        ]);

        return view('admin.reports.index', [
            'monthly' => $monthly,
            'summary' => [
                'received_year' => Payment::query()->whereYear('paid_on', now()->year)->sum('amount'),
                'invoiced_year' => Invoice::query()->where('status', 'issued')->whereYear('issue_date', now()->year)->sum('total'),
                'outstanding' => Invoice::query()->outstanding()->selectRaw('sum(total - amount_paid) as owed')->value('owed') ?? 0,
                'average_invoice' => Invoice::query()->where('status', 'issued')->avg('total') ?? 0,
            ],
            'aging' => $this->aging(),
            'topItems' => $this->topItems(),
            'quotes' => $this->quoteFunnel(),
            'inventory' => [
                'cost_value' => InventoryItem::query()->where('is_active', true)->where('quantity', '>', 0)->selectRaw('sum(quantity * cost_price) as v')->value('v') ?? 0,
                'sale_value' => InventoryItem::query()->where('is_active', true)->where('quantity', '>', 0)->selectRaw('sum(quantity * sale_price) as v')->value('v') ?? 0,
                'low' => InventoryItem::query()->where('is_active', true)->lowStock()->orderBy('quantity')->limit(8)->get(),
            ],
        ]);
    }

    /**
     * Money owed, grouped by how far past the due date it is.
     *
     * @return array<string, float>
     */
    private function aging(): array
    {
        $buckets = ['Not yet due' => 0.0, '1–30 days' => 0.0, '31–60 days' => 0.0, '61–90 days' => 0.0, 'Over 90 days' => 0.0];

        foreach (Invoice::query()->outstanding()->get() as $invoice) {
            $late = $invoice->due_date && $invoice->due_date->isPast() ? (int) $invoice->due_date->diffInDays(today()) : 0;
            $bucket = match (true) {
                $late <= 0 => 'Not yet due',
                $late <= 30 => '1–30 days',
                $late <= 60 => '31–60 days',
                $late <= 90 => '61–90 days',
                default => 'Over 90 days',
            };
            $buckets[$bucket] = Money::round($buckets[$bucket] + $invoice->balance());
        }

        return $buckets;
    }

    /**
     * Best-selling lines on issued invoices, by revenue.
     */
    private function topItems()
    {
        $issued = Invoice::query()->where('status', 'issued')->pluck('id');

        return LineItem::query()
            ->where('document_type', (new Invoice)->getMorphClass())
            ->whereIn('document_id', $issued)
            ->selectRaw('coalesce(inventory_item_id, 0) as item_key, min(description) as description, sum(quantity) as quantity, sum(line_total) as revenue')
            ->groupByRaw('coalesce(inventory_item_id, 0), case when inventory_item_id is null then description else \'\' end')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteFunnel(): array
    {
        $counts = Quote::query()->selectRaw('status, count(*) as total, sum(total) as value')->groupBy('status')->get()->keyBy('status');
        $decided = collect(['accepted', 'declined', 'invoiced'])->sum(fn ($s) => $counts[$s]->total ?? 0);
        $won = collect(['accepted', 'invoiced'])->sum(fn ($s) => $counts[$s]->total ?? 0);

        return [
            'counts' => collect(Quote::STATUSES)->map(fn ($status, $key) => ['label' => $status[0], 'count' => (int) ($counts[$key]->total ?? 0), 'value' => (float) ($counts[$key]->value ?? 0)]),
            'win_rate' => $decided ? round($won / $decided * 100) : null,
        ];
    }
}
