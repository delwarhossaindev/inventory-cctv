<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view sales');
    }

    public function index()
    {
        $returns = SaleReturn::with('sale.customer')
            ->latest('return_date')
            ->latest('id')
            ->paginate(20);

        return view('admin.returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load('items.product');

        // How much of each product has already been returned for this sale.
        $returnedByProduct = $this->returnedByProduct($sale);

        return view('admin.returns.create', compact('sale', 'returnedByProduct'));
    }

    /**
     * Quantity already returned for this sale, keyed by product_id.
     */
    private function returnedByProduct(Sale $sale)
    {
        return SaleReturnItem::whereIn('sale_return_id', $sale->returns()->pluck('id'))
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');
    }

    public function store(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Guard: never let cumulative returns exceed what was actually sold.
        $soldByProduct = $sale->items()
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')->pluck('qty', 'product_id');
        $alreadyByProduct = $this->returnedByProduct($sale);

        $requestedByProduct = [];
        foreach ($data['items'] as $row) {
            if ((int) $row['quantity'] <= 0) {
                continue;
            }
            $pid = $row['product_id'];
            $requestedByProduct[$pid] = ($requestedByProduct[$pid] ?? 0) + (int) $row['quantity'];
        }

        foreach ($requestedByProduct as $pid => $qty) {
            $sold = (int) ($soldByProduct[$pid] ?? 0);
            $already = (int) ($alreadyByProduct[$pid] ?? 0);
            $remaining = max($sold - $already, 0);

            if ($qty > $remaining) {
                $name = optional(Product::find($pid))->name ?? 'this product';
                return back()->withInput()->withErrors([
                    'items' => "Cannot return {$qty} of \"{$name}\" — only {$remaining} unit(s) left to return"
                        . ($already > 0 ? " ({$already} already returned)." : '.'),
                ]);
            }
        }

        $return = DB::transaction(function () use ($sale, $data) {
            $total = 0;
            $lines = [];

            foreach ($data['items'] as $row) {
                if ($row['quantity'] <= 0) {
                    continue;
                }
                $lineSub = $row['quantity'] * $row['unit_price'];
                $total += $lineSub;
                $lines[] = [
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $lineSub,
                ];
            }

            $return = $sale->returns()->create([
                'return_date' => $data['return_date'],
                'total' => $total,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $return->items()->create($line);

                $product = Product::find($line['product_id']);
                if ($product) {
                    $unitCost = (float) $product->purchase_price;
                    $product->stockIn(
                        $line['quantity'],
                        $unitCost,
                        'return',
                        $return,
                        'Sale return from ' . $sale->invoice_no
                    );
                }
            }

            return $return;
        });

        return redirect()->route('admin.returns.index')
            ->with('success', 'Return processed for ' . $sale->invoice_no . ' — ৳' . number_format($return->total, 2));
    }

    public function show(SaleReturn $return)
    {
        $return->load(['sale.customer', 'items.product']);

        return view('admin.returns.show', compact('return'));
    }
}
