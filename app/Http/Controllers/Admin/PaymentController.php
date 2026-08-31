<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view sales');
    }

    public function index(Request $request)
    {
        $query = Payment::with('payable')->latest('payment_date')->latest('id');

        if ($request->filled('type')) {
            $map = ['sale' => Sale::class, 'purchase' => Purchase::class];
            if (isset($map[$request->type])) {
                $query->where('payable_type', $map[$request->type]);
            }
        }

        $allDueSales = Sale::with('customer')->where('due', '>', 0)->latest('sale_date')->get();
        $allDuePurchases = Purchase::with('supplier')->where('due', '>', 0)->latest('purchase_date')->get();

        // Totals stay accurate (full business due); the search only narrows the lists.
        $receivable = $allDueSales->sum('due');
        $payable = $allDuePurchases->sum('due');

        $q = strtolower(trim((string) $request->get('q')));

        $dueSales = $q === '' ? $allDueSales : $allDueSales->filter(fn ($s) =>
            str_contains(strtolower($s->invoice_no), $q)
            || str_contains(strtolower(optional($s->customer)->name ?? ''), $q)
        )->values();

        $duePurchases = $q === '' ? $allDuePurchases : $allDuePurchases->filter(fn ($p) =>
            str_contains(strtolower($p->invoice_no), $q)
            || str_contains(strtolower(optional($p->supplier)->name ?? ''), $q)
        )->values();

        // Customers (named, not walk-in) with outstanding due — for customer-wise collection.
        $dueCustomers = $allDueSales->filter(fn ($s) => $s->customer_id)
            ->groupBy('customer_id')
            ->map(fn ($g) => (object) [
                'id' => $g->first()->customer_id,
                'name' => optional($g->first()->customer)->name ?? 'Customer',
                'phone' => optional($g->first()->customer)->phone,
                'due' => $g->sum('due'),
                'count' => $g->count(),
            ])
            ->sortByDesc('due')
            ->values();

        return view('admin.payments.index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'dueSales' => $dueSales,
            'duePurchases' => $duePurchases,
            'dueCustomers' => $dueCustomers,
            'receivable' => $receivable,
            'payable' => $payable,
            'q' => $request->get('q'),
        ]);
    }

    public function storeSalePayment(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->due,
            'method' => 'required|in:cash,card,mobile',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $sale->payments()->create($data + ['created_by' => auth()->id()]);
        $sale->recalculateDue();

        return back()->with('success', 'Payment of ৳' . number_format($data['amount'], 2) . ' recorded.');
    }

    public function storePurchasePayment(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $purchase->due,
            'method' => 'required|in:cash,card,mobile',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $purchase->payments()->create($data + ['created_by' => auth()->id()]);
        $purchase->recalculateDue();

        return back()->with('success', 'Payment of ৳' . number_format($data['amount'], 2) . ' recorded.');
    }

    /**
     * Collect one amount from a customer and distribute it across their
     * unpaid invoices, oldest first (FIFO).
     */
    public function storeCustomerPayment(Request $request, Customer $customer)
    {
        $dueSales = $customer->sales()
            ->where('due', '>', 0)
            ->orderBy('sale_date')->orderBy('id')
            ->get();

        $totalDue = (float) $dueSales->sum('due');

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $totalDue,
            'method' => 'required|in:cash,card,mobile',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        if ($totalDue <= 0) {
            return back()->withErrors(['amount' => 'This customer has no outstanding due.']);
        }

        $allocations = DB::transaction(function () use ($dueSales, $data) {
            $remaining = (float) $data['amount'];
            $count = 0;

            foreach ($dueSales as $sale) {
                if ($remaining <= 0) {
                    break;
                }
                $apply = min($remaining, (float) $sale->due);

                $sale->payments()->create([
                    'amount' => $apply,
                    'method' => $data['method'],
                    'payment_date' => $data['payment_date'],
                    'note' => $data['note'] ?: 'Customer payment',
                    'created_by' => auth()->id(),
                ]);
                $sale->recalculateDue();

                $remaining -= $apply;
                $count++;
            }

            return $count;
        });

        return back()->with('success', '৳' . number_format($data['amount'], 2)
            . ' collected from ' . $customer->name . ' across ' . $allocations . ' invoice(s).');
    }
}
