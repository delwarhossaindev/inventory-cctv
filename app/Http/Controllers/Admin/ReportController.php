<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Support\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view reports');
    }

    public function sales(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        $sales = Sale::with('customer')
            ->withSum('items as revenue', 'subtotal')
            ->withSum('items as cogs', 'cost_total')
            ->whereBetween('sale_date', [$from, $to])
            ->latest('sale_date')
            ->get();

        $totals = [
            'count' => $sales->count(),
            'revenue' => $sales->sum('revenue'),
            'cogs' => $sales->sum('cogs'),
            'profit' => $sales->sum('revenue') - $sales->sum('cogs'),
            'collected' => $sales->sum('total'),
            'due' => $sales->sum('due'),
        ];

        if ($request->boolean('pdf')) {
            return $this->reportPdf(
                'Sales Report',
                $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                ['Date', 'Invoice', 'Customer', 'Revenue', 'COGS', 'Profit', 'Collected', 'Due'],
                ['left', 'left', 'left', 'right', 'right', 'right', 'right', 'right'],
                $sales->map(fn ($s) => [
                    $s->sale_date?->format('d-m-Y'),
                    $s->invoice_no,
                    optional($s->customer)->name ?: 'Walk-in',
                    $this->money($s->revenue),
                    $this->money($s->cogs),
                    $this->money($s->revenue - $s->cogs),
                    $this->money($s->total),
                    $this->money($s->due),
                ])->all(),
                [$totals['count'] . ' sales', '', 'Totals', $this->money($totals['revenue']), $this->money($totals['cogs']),
                    $this->money($totals['profit']), $this->money($totals['collected']), $this->money($totals['due'])],
            );
        }

        return view('admin.reports.sales', [
            'sales' => $sales,
            'totals' => $totals,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function stock(Request $request)
    {
        // FIFO inventory value = sum of remaining batch quantities × their unit cost.
        $batchValue = DB::table('stock_batches')
            ->select('product_id', DB::raw('SUM(remaining) as qty'), DB::raw('SUM(remaining * unit_cost) as value'))
            ->where('remaining', '>', 0)
            ->groupBy('product_id');

        $query = Product::query()
            ->leftJoinSub($batchValue, 'b', 'b.product_id', '=', 'products.id')
            ->select('products.*', DB::raw('COALESCE(b.value, 0) as stock_value'))
            ->orderByDesc('stock_value');

        if ($request->boolean('low')) {
            $query->lowStock();
        }

        $products = $query->get();

        $totals = [
            'products' => $products->count(),
            'units' => $products->sum('stock_quantity'),
            'value' => $products->sum('stock_value'),
            'retail' => $products->sum(fn ($p) => $p->stock_quantity * $p->sale_price),
        ];

        if ($request->boolean('pdf')) {
            return $this->reportPdf(
                'Stock Valuation Report',
                null,
                ['Product', 'Unit', 'Stock Qty', 'Stock Value', 'Retail Value'],
                ['left', 'left', 'right', 'right', 'right'],
                $products->map(fn ($p) => [
                    $p->name,
                    $p->unit,
                    $p->stock_quantity,
                    $this->money($p->stock_value),
                    $this->money($p->stock_quantity * $p->sale_price),
                ])->all(),
                ['Totals (' . $totals['products'] . ' products)', '', $totals['units'],
                    $this->money($totals['value']), $this->money($totals['retail'])],
            );
        }

        return view('admin.reports.stock', compact('products', 'totals'));
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->range($request);

        $query = Purchase::with('supplier')->whereBetween('purchase_date', [$from, $to])->latest('purchase_date');
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        $purchases = $query->get();

        $totals = [
            'count' => $purchases->count(),
            'total' => $purchases->sum('total'),
            'paid' => $purchases->sum('paid'),
            'due' => $purchases->sum('due'),
        ];

        if ($request->boolean('pdf')) {
            return $this->reportPdf(
                'Purchases Report',
                $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                ['Date', 'Invoice', 'Supplier', 'Total', 'Paid', 'Due'],
                ['left', 'left', 'left', 'right', 'right', 'right'],
                $purchases->map(fn ($p) => [
                    $p->purchase_date?->format('d-m-Y'),
                    $p->invoice_no,
                    optional($p->supplier)->name ?: '—',
                    $this->money($p->total),
                    $this->money($p->paid),
                    $this->money($p->due),
                ])->all(),
                [$totals['count'] . ' purchases', '', 'Totals',
                    $this->money($totals['total']), $this->money($totals['paid']), $this->money($totals['due'])],
            );
        }

        return view('admin.reports.purchases', [
            'purchases' => $purchases,
            'totals' => $totals,
            'suppliers' => Supplier::orderBy('name')->get(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function products(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->groupBy('products.id', 'products.name')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as qty'),
                DB::raw('SUM(sale_items.subtotal) as revenue'),
                DB::raw('SUM(sale_items.cost_total) as cogs')
            )
            ->orderByDesc('qty')
            ->get();

        $totals = [
            'qty' => $rows->sum('qty'),
            'revenue' => $rows->sum('revenue'),
            'cogs' => $rows->sum('cogs'),
            'profit' => $rows->sum('revenue') - $rows->sum('cogs'),
        ];

        if ($request->boolean('pdf')) {
            return $this->reportPdf(
                'Top Products Report',
                $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                ['Product', 'Qty Sold', 'Revenue', 'COGS', 'Profit'],
                ['left', 'right', 'right', 'right', 'right'],
                $rows->map(fn ($r) => [
                    $r->name,
                    $r->qty,
                    $this->money($r->revenue),
                    $this->money($r->cogs),
                    $this->money($r->revenue - $r->cogs),
                ])->all(),
                ['Totals', $totals['qty'], $this->money($totals['revenue']),
                    $this->money($totals['cogs']), $this->money($totals['profit'])],
            );
        }

        return view('admin.reports.products', [
            'rows' => $rows,
            'totals' => $totals,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function daily(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::withSum('items as revenue', 'subtotal')
            ->withSum('items as cogs', 'cost_total')
            ->whereBetween('sale_date', [$from, $to])
            ->get();

        $days = $sales->groupBy(fn ($s) => $s->sale_date->toDateString())
            ->map(fn ($g) => [
                'count' => $g->count(),
                'revenue' => $g->sum('revenue'),
                'cogs' => $g->sum('cogs'),
                'profit' => $g->sum('revenue') - $g->sum('cogs'),
                'collected' => $g->sum('total'),
            ])
            ->sortKeysDesc();

        $totals = [
            'count' => $sales->count(),
            'revenue' => $sales->sum('revenue'),
            'profit' => $sales->sum('revenue') - $sales->sum('cogs'),
            'collected' => $sales->sum('total'),
        ];

        if ($request->boolean('pdf')) {
            return $this->reportPdf(
                'Daily Summary Report',
                $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                ['Date', 'Sales', 'Revenue', 'Profit', 'Collected'],
                ['left', 'right', 'right', 'right', 'right'],
                $days->map(fn ($d, $date) => [
                    \Illuminate\Support\Carbon::parse($date)->format('d-m-Y'),
                    $d['count'],
                    $this->money($d['revenue']),
                    $this->money($d['profit']),
                    $this->money($d['collected']),
                ])->values()->all(),
                ['Totals', $totals['count'], $this->money($totals['revenue']),
                    $this->money($totals['profit']), $this->money($totals['collected'])],
            );
        }

        return view('admin.reports.daily', [
            'days' => $days,
            'totals' => $totals,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::whereBetween('sale_date', [$from, $to]);
        $revenue = (clone $sales)->sum('total');
        $salesCount = (clone $sales)->count();

        $cogs = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->sum('sale_items.cost_total');

        $returns = SaleReturn::whereBetween('return_date', [$from, $to])->sum('total');

        $purchases = Purchase::whereBetween('purchase_date', [$from, $to])->sum('total');

        $expenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');

        $expenseByCategory = Expense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->groupBy(fn ($e) => optional($e->category)->name ?: 'Uncategorized')
            ->map(fn ($g) => $g->sum('amount'))
            ->sortDesc();

        $grossProfit = $revenue - $cogs - $returns;
        $netProfit = $grossProfit - $expenses;

        if ($request->boolean('pdf')) {
            $rows = [
                ['Sales Revenue (' . $salesCount . ' sales)', $this->money($revenue)],
                ['Less: Cost of Goods Sold', '(' . $this->money($cogs) . ')'],
                ['Less: Sale Returns', '(' . $this->money($returns) . ')'],
                ['<b>Gross Profit</b>', '<b>' . $this->money($grossProfit) . '</b>'],
            ];
            foreach ($expenseByCategory as $cat => $amt) {
                $rows[] = ['Expense: ' . $cat, '(' . $this->money($amt) . ')'];
            }
            $rows[] = ['<b>Total Expenses</b>', '<b>(' . $this->money($expenses) . ')</b>'];
            $rows[] = ['Purchases (info)', $this->money($purchases)];

            return $this->reportPdf(
                'Profit & Loss Report',
                $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                ['Description', 'Amount'],
                ['left', 'right'],
                $rows,
                ['Net Profit', $this->money($netProfit)],
            );
        }

        return view('admin.reports.profit-loss', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'revenue' => $revenue,
            'salesCount' => $salesCount,
            'cogs' => $cogs,
            'returns' => $returns,
            'grossProfit' => $grossProfit,
            'expenses' => $expenses,
            'expenseByCategory' => $expenseByCategory,
            'purchases' => $purchases,
            'netProfit' => $netProfit,
        ]);
    }

    /** Render a generic report table as a PDF. */
    private function reportPdf(string $title, ?string $period, array $head, array $align, array $rows, ?array $foot = null)
    {
        return Pdf::render(
            'pdf.report',
            compact('title', 'period', 'head', 'align', 'rows', 'foot'),
            str_replace(' ', '-', $title) . '.pdf',
            request()->boolean('download'),
        );
    }

    private function money($v): string
    {
        return number_format((float) $v, 2);
    }

    /** Resolve a [from, to] date range from the request, defaulting to the current month. */
    private function range(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}
