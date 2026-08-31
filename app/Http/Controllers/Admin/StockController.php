<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view stock')->only(['index', 'movements']);
        $this->middleware('permission:adjust stock')->only(['adjust', 'storeAdjust']);
    }

    public function index(Request $request)
    {
        $query = Product::query()->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('model', 'like', "%{$q}%"));
        }
        if ($request->boolean('low')) {
            $query->lowStock();
        }

        $products = $query->paginate(20)->withQueryString();

        $agg = Product::selectRaw(
            'SUM(stock_quantity) as total_items, SUM(stock_quantity * purchase_price) as stock_value,'
            . ' SUM(CASE WHEN stock_quantity <= alert_quantity THEN 1 ELSE 0 END) as low_stock'
        )->first();

        $summary = [
            'total_items' => (int) $agg->total_items,
            'stock_value' => (float) ($agg->stock_value ?? 0),
            'low_stock' => (int) $agg->low_stock,
        ];

        return view('admin.stock.index', compact('products', 'summary'));
    }

    public function adjust(Product $product)
    {
        return view('admin.stock.adjust', compact('product'));
    }

    public function storeAdjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:set,add,subtract'],
            'value' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($product, $data) {
            $current = $product->stock_quantity;

            $delta = match ($data['mode']) {
                'set' => $data['value'] - $current,
                'add' => $data['value'],
                'subtract' => -$data['value'],
            };

            $note = $data['note'] ?: 'Manual stock adjustment';
            if ($delta > 0) {
                $product->stockIn($delta, (float) $product->purchase_price, 'adjustment', null, $note);
            } elseif ($delta < 0) {
                $product->stockOut(-$delta, 'adjustment', null, $note);
            }
        });

        ActivityLog::log('stock_adjusted', 'Adjusted stock for ' . $product->name, $product);

        return redirect()->route('admin.stock.index')->with('success', 'Stock adjusted for ' . $product->name . '.');
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with('product')->latest();
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return view('admin.stock.movements', [
            'movements' => $query->paginate(25)->withQueryString(),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
