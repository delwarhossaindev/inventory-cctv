<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Sale;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view sales');
    }

    public function index()
    {
        $plans = InstallmentPlan::with(['customer', 'sale'])->latest()->paginate(20);

        return view('admin.installments.index', compact('plans'));
    }

    public function create(Sale $sale)
    {
        if (!$sale->customer_id) {
            return back()->with('error', 'Installment requires a named customer.');
        }

        return view('admin.installments.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'down_payment' => 'required|numeric|min:0',
            'num_installments' => 'required|integer|min:1|max:60',
        ]);

        $remaining = $sale->total - $data['down_payment'];
        $installmentAmount = round($remaining / $data['num_installments'], 2);

        $plan = InstallmentPlan::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'total_amount' => $sale->total,
            'down_payment' => $data['down_payment'],
            'num_installments' => $data['num_installments'],
            'installment_amount' => $installmentAmount,
        ]);

        for ($i = 1; $i <= $data['num_installments']; $i++) {
            $plan->payments()->create([
                'installment_no' => $i,
                'due_date' => now()->addMonths($i)->toDateString(),
                'amount' => $installmentAmount,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('admin.installments.index')->with('success', 'Installment plan created.');
    }

    public function show(InstallmentPlan $installment)
    {
        $installment->load(['customer', 'sale', 'payments']);

        return view('admin.installments.show', compact('installment'));
    }

    public function markPaid(InstallmentPayment $payment)
    {
        $payment->update(['status' => 'paid', 'paid_date' => now()]);

        $plan = $payment->plan;
        if ($plan->payments()->where('status', 'pending')->count() === 0) {
            $plan->update(['status' => 'completed']);
        }

        return back()->with('success', 'Installment #' . $payment->installment_no . ' marked as paid.');
    }
}
