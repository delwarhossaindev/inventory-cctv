<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Support\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view reports');
    }

    /**
     * Cash-basis day book: money in vs money out per day, with a running balance.
     */
    public function dayBook(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        // --- In-range daily totals ---
        $incomeByDay = $this->merge([
            $this->dailySum(Sale::query(), 'sale_date', 'paid', $from, $to),
            $this->dailySum(Payment::where('payable_type', Sale::class), 'payment_date', 'amount', $from, $to),
        ]);

        $expenseByDay = $this->merge([
            $this->dailySum(Expense::query(), 'expense_date', 'amount', $from, $to),
            $this->dailySum(Purchase::query(), 'purchase_date', 'paid', $from, $to),
            $this->dailySum(Payment::where('payable_type', Purchase::class), 'payment_date', 'amount', $from, $to),
            $this->dailySum(SaleReturn::query(), 'return_date', 'total', $from, $to),
        ]);

        // --- Opening balance (everything before the range) ---
        $opening = $this->totalBefore(Sale::query(), 'sale_date', 'paid', $from)
            + $this->totalBefore(Payment::where('payable_type', Sale::class), 'payment_date', 'amount', $from)
            - $this->totalBefore(Expense::query(), 'expense_date', 'amount', $from)
            - $this->totalBefore(Purchase::query(), 'purchase_date', 'paid', $from)
            - $this->totalBefore(Payment::where('payable_type', Purchase::class), 'payment_date', 'amount', $from)
            - $this->totalBefore(SaleReturn::query(), 'return_date', 'total', $from);

        // --- Build per-day rows with a running balance ---
        $dates = collect(array_keys($incomeByDay + $expenseByDay))->unique()->sort()->values();

        $balance = $opening;
        $rows = [];
        foreach ($dates as $d) {
            $in = $incomeByDay[$d] ?? 0;
            $out = $expenseByDay[$d] ?? 0;
            $balance += $in - $out;
            $rows[] = [
                'date' => $d,
                'income' => $in,
                'expense' => $out,
                'net' => $in - $out,
                'balance' => $balance,
            ];
        }

        $totals = [
            'income' => array_sum($incomeByDay),
            'expense' => array_sum($expenseByDay),
            'net' => array_sum($incomeByDay) - array_sum($expenseByDay),
            'opening' => $opening,
            'closing' => $balance,
        ];

        // Source-wise breakdown of what makes up income and expense.
        $breakdown = [
            'income' => [
                'Cash sales' => (float) Sale::whereBetween('sale_date', [$from, $to])->sum('paid'),
                'Due collections' => (float) Payment::where('payable_type', Sale::class)->whereBetween('payment_date', [$from, $to])->sum('amount'),
            ],
            'expense' => [
                'Expenses' => (float) Expense::whereBetween('expense_date', [$from, $to])->sum('amount'),
                'Purchase payments' => (float) Purchase::whereBetween('purchase_date', [$from, $to])->sum('paid')
                    + (float) Payment::where('payable_type', Purchase::class)->whereBetween('payment_date', [$from, $to])->sum('amount'),
                'Sale returns (refund)' => (float) SaleReturn::whereBetween('return_date', [$from, $to])->sum('total'),
            ],
        ];

        // PDF export
        if ($request->boolean('pdf')) {
            $pdfRows = [['Opening Balance', '', '', '', number_format($totals['opening'], 2)]];
            foreach ($rows as $r) {
                $pdfRows[] = [
                    Carbon::parse($r['date'])->format('d-m-Y'),
                    number_format($r['income'], 2),
                    number_format($r['expense'], 2),
                    number_format($r['net'], 2),
                    number_format($r['balance'], 2),
                ];
            }

            return Pdf::render('pdf.report', [
                'title' => 'Day Book',
                'period' => $from->format('d M Y') . ' — ' . $to->format('d M Y'),
                'head' => ['Date', 'Income', 'Expense', 'Net', 'Balance'],
                'align' => ['left', 'right', 'right', 'right', 'right'],
                'rows' => $pdfRows,
                'foot' => ['Total', number_format($totals['income'], 2), number_format($totals['expense'], 2),
                    number_format($totals['net'], 2), number_format($totals['closing'], 2)],
            ], 'Day-Book.pdf', $request->boolean('download'));
        }

        return view('admin.accounting.day-book', [
            'rows' => $rows,
            'totals' => $totals,
            'breakdown' => $breakdown,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    /**
     * Trial balance: snapshot of all account balances up to a given date.
     */
    public function trialBalance(Request $request)
    {
        $asAt = $request->filled('date')
            ? Carbon::parse($request->date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Cash collected from customers (initial paid + later payments)
        $salesCollected = (float) Sale::where('sale_date', '<=', $asAt)->sum('paid')
            + (float) Payment::where('payable_type', Sale::class)->where('payment_date', '<=', $asAt)->sum('amount');

        // Cash paid to suppliers (initial paid + later payments)
        $purchasesPaid = (float) Purchase::where('purchase_date', '<=', $asAt)->sum('paid')
            + (float) Payment::where('payable_type', Purchase::class)->where('payment_date', '<=', $asAt)->sum('amount');

        $expensesTotal = (float) Expense::where('expense_date', '<=', $asAt)->sum('amount');
        $returnsTotal  = (float) SaleReturn::where('return_date', '<=', $asAt)->sum('total');

        // Net cash position (may be negative = overdraft)
        $cash        = $salesCollected - $purchasesPaid - $expensesTotal - $returnsTotal;
        $revenue     = (float) Sale::where('sale_date', '<=', $asAt)->sum('total');
        $receivables = max($revenue - $salesCollected, 0);
        $inventory   = (float) DB::table('stock_batches')->sum(DB::raw('remaining * unit_cost'));
        $cogs        = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.sale_date', '<=', $asAt)
            ->sum('sale_items.cost_total');
        $totalPurchases = (float) Purchase::where('purchase_date', '<=', $asAt)->sum('total');
        $payables       = max($totalPurchases - $purchasesPaid, 0);

        // Build debit accounts
        $debit = [];
        if ($cash >= 0) {
            $debit[] = ['Cash & Bank', $cash, 'Asset'];
        }
        $debit[] = ['Accounts Receivable', $receivables, 'Asset'];
        $debit[] = ['Closing Inventory (FIFO)', $inventory, 'Asset'];
        $debit[] = ['Cost of Goods Sold', $cogs, 'Expense'];
        $debit[] = ['Operating Expenses', $expensesTotal, 'Expense'];
        if ($returnsTotal > 0) {
            $debit[] = ['Sale Returns', $returnsTotal, 'Contra'];
        }

        // Build credit accounts
        $credit = [];
        $credit[] = ['Sales Revenue', $revenue, 'Revenue'];
        if ($payables > 0) {
            $credit[] = ['Accounts Payable', $payables, 'Liability'];
        }
        if ($cash < 0) {
            $credit[] = ['Bank Overdraft', abs($cash), 'Liability'];
        }

        $totalDr = array_sum(array_column($debit, 1));
        $totalCr = array_sum(array_column($credit, 1));

        // Owner's equity is the balancing figure (capital + retained earnings)
        $equity = $totalDr - $totalCr;
        if ($equity > 0) {
            $credit[] = ["Owner's Equity", $equity, 'Equity'];
            $totalCr += $equity;
        } elseif ($equity < 0) {
            $debit[] = ['Accumulated Deficit', abs($equity), 'Loss'];
            $totalDr += abs($equity);
        }

        $grossProfit = $revenue - $returnsTotal - $cogs;
        $netProfit   = $grossProfit - $expensesTotal;

        if ($request->boolean('pdf')) {
            $maxRows = max(count($debit), count($credit));
            $pdfRows = [];
            for ($i = 0; $i < $maxRows; $i++) {
                $dr = $debit[$i] ?? null;
                $cr = $credit[$i] ?? null;
                $pdfRows[] = [
                    $dr ? $dr[0] : '',
                    $dr ? number_format($dr[1], 2) : '',
                    $cr ? $cr[0] : '',
                    $cr ? number_format($cr[1], 2) : '',
                ];
            }

            return Pdf::render('pdf.report', [
                'title'  => 'Trial Balance',
                'period' => 'As at ' . $asAt->format('d M Y'),
                'head'   => ['Particulars (Dr)', 'Amount (৳)', 'Particulars (Cr)', 'Amount (৳)'],
                'align'  => ['left', 'right', 'left', 'right'],
                'rows'   => $pdfRows,
                'foot'   => ['Total', number_format($totalDr, 2), 'Total', number_format($totalCr, 2)],
            ], 'Trial-Balance.pdf', $request->boolean('download'));
        }

        return view('admin.accounting.trial-balance', [
            'asAt'        => $asAt->toDateString(),
            'debit'       => $debit,
            'credit'      => $credit,
            'totalDr'     => $totalDr,
            'totalCr'     => $totalCr,
            'revenue'     => $revenue,
            'cogs'        => $cogs,
            'returns'     => $returnsTotal,
            'expenses'    => $expensesTotal,
            'grossProfit' => $grossProfit,
            'netProfit'   => $netProfit,
            'inventory'   => $inventory,
            'receivables' => $receivables,
            'payables'    => $payables,
            'cash'        => $cash,
        ]);
    }

    /** Daily SUM of a column, keyed by Y-m-d, within a date range. */
    private function dailySum(Builder $query, string $dateCol, string $amountCol, $from, $to): array
    {
        return $query->whereBetween($dateCol, [$from, $to])
            ->selectRaw("DATE($dateCol) as d, SUM($amountCol) as v")
            ->groupBy('d')
            ->pluck('v', 'd')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    /** Total of a column for everything strictly before a date. */
    private function totalBefore(Builder $query, string $dateCol, string $amountCol, $before): float
    {
        return (float) $query->where($dateCol, '<', $before)->sum($amountCol);
    }

    /** Merge several [date => amount] maps by summing matching dates. */
    private function merge(array $maps): array
    {
        $out = [];
        foreach ($maps as $map) {
            foreach ($map as $d => $v) {
                $out[$d] = ($out[$d] ?? 0) + $v;
            }
        }

        return $out;
    }
}
