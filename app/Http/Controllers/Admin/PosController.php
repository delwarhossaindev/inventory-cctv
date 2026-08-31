<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:access pos');
    }

    public function index()
    {
        // Map of batch number -> product id, so a scanned batch barcode resolves to its product.
        $batchMap = StockBatch::where('remaining', '>', 0)
            ->whereNotNull('batch_no')
            ->orderByDesc('received_at')
            ->pluck('product_id', 'batch_no');

        return view('admin.pos.index', [
            'products' => Product::active()
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'barcode', 'model', 'sale_price', 'stock_quantity', 'image_url', 'main_category_id']),
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
            'batchMap' => $batchMap,
        ]);
    }

    /** POS 2 — search-driven, no pre-loaded product grid. */
    public function pos2()
    {
        return view('admin.pos.pos2', [
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    /** AJAX product search for POS 2 (also resolves batch numbers). */
    public function searchProducts(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        // If q exactly matches a batch number, return that product only
        $batchPid = StockBatch::where('batch_no', $q)->where('remaining', '>', 0)->value('product_id');

        $query = Product::active()->orderBy('name')->limit(24);

        if ($batchPid) {
            $query->where('id', $batchPid);
        } else {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%");
            });
        }

        return response()->json(
            $query->get(['id', 'name', 'sku', 'barcode', 'sale_price', 'stock_quantity', 'image_url'])
        );
    }

    /** Quick-create a customer from the POS screen (returns JSON). */
    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $customer = Customer::create($data + ['status' => 'active']);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,card,mobile,due'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [], ['items' => 'cart']);

        $sale = DB::transaction(function () use ($data) {
            $subtotal = 0;
            $lines = [];

            foreach ($data['items'] as $row) {
                $product = Product::lockForUpdate()->find($row['product_id']);

                if ($product->stock_quantity < $row['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for \"{$product->name}\" (available: {$product->stock_quantity}).",
                    ]);
                }

                $lineSub = $row['quantity'] * $row['unit_price'];
                $subtotal += $lineSub;
                $lines[] = [
                    'product' => $product,
                    'data' => [
                        'product_id' => $product->id,
                        'quantity' => $row['quantity'],
                        'unit_price' => $row['unit_price'],
                        'subtotal' => $lineSub,
                    ],
                ];
            }

            $total = $subtotal - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);
            $paid = $data['paid'] ?? $total;

            $sale = Sale::create([
                'invoice_no' => $this->nextInvoiceNo(),
                'customer_id' => $data['customer_id'] ?? null,
                'sale_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'total' => $total,
                'paid' => $paid,
                'due' => max($total - $paid, 0),
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'source' => 'pos',
                'note' => $data['note'] ?? null,
            ]);

            foreach ($lines as $line) {
                // FIFO stock-out returns the cost of goods sold for this line.
                $cogs = $line['product']->stockOut($line['data']['quantity'], 'sale', $sale, 'Sale ' . $sale->invoice_no);
                $warrantyExpires = $line['product']->warranty_days ? now()->addDays($line['product']->warranty_days)->toDateString() : null;
                $sale->items()->create($line['data'] + ['cost_total' => $cogs, 'warranty_expires' => $warrantyExpires]);
            }

            return $sale;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'redirect' => route('admin.sales.show', $sale),
                'invoice_no' => $sale->invoice_no,
            ]);
        }

        ActivityLog::log('sale_created', 'Created sale ' . $sale->invoice_no . ' — ৳' . number_format($sale->total, 2), $sale);

        return redirect()->route('admin.sales.invoice', $sale)->with('success', 'Sale completed: ' . $sale->invoice_no);
    }

    private function nextInvoiceNo(): string
    {
        return 'INV-' . str_pad((string) (Sale::max('id') + 1), 5, '0', STR_PAD_LEFT);
    }
}
