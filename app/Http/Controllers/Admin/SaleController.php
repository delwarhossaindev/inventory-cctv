<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Support\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view sales')->only(['index', 'show']);
        $this->middleware('permission:delete sales')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Sale::with('customer')->latest();
        if ($request->filled('q')) {
            $query->where('invoice_no', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return view('admin.sales.index', ['sales' => $query->paginate(15)->withQueryString()]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments']);

        return view('admin.sales.show', compact('sale'));
    }

    public function invoice(Sale $sale, Request $request)
    {
        $sale->load(['customer', 'items.product']);
        $settings = Setting::getAll();

        return Pdf::render(
            'pdf.invoice',
            compact('sale', 'settings'),
            'Invoice-' . $sale->invoice_no . '.pdf',
            $request->boolean('download'),
        );
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        $settings = Setting::getAll();

        return view('admin.sales.receipt', compact('sale', 'settings'));
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale->load('items');

            // Return the sold quantities back to stock as a new batch,
            // priced at the original FIFO cost so valuation stays correct.
            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $unitCost = $item->quantity > 0 ? (float) $item->cost_total / $item->quantity : (float) $product->purchase_price;
                    $product->stockIn($item->quantity, $unitCost, 'adjustment', $sale, 'Reversal of sale ' . $sale->invoice_no);
                }
            }

            ActivityLog::log('sale_deleted', 'Deleted sale ' . $sale->invoice_no . ' — ৳' . number_format($sale->total, 2));
            $sale->delete();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted and stock returned.');
    }
}
