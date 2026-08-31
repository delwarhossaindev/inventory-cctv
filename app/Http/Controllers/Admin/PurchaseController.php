<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view purchases')->only(['index', 'show']);
        $this->middleware('permission:create purchases')->only(['create', 'store', 'bulkCreate', 'bulkStore', 'importTemplate']);
        $this->middleware('permission:delete purchases')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Purchase::with('supplier')->latest();
        if ($request->filled('q')) {
            $query->where('invoice_no', 'like', '%' . $request->q . '%');
        }

        return view('admin.purchases.index', ['purchases' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.purchases.create', [
            'suppliers' => Supplier::where('status', 'active')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku', 'model', 'purchase_price', 'stock_quantity']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $purchase = DB::transaction(function () use ($data) {
            $computed = $this->computeTotals($data);

            $purchase = Purchase::create([
                'invoice_no' => $this->nextInvoiceNo(),
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $computed['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'total' => $computed['total'],
                'paid' => $data['paid'] ?? 0,
                'due' => $computed['total'] - ($data['paid'] ?? 0),
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($computed['items'] as $item) {
                $purchase->items()->create($item);

                // Stock only moves when goods are actually received.
                // Each received line becomes a FIFO batch at its unit cost.
                if ($data['status'] === 'received') {
                    Product::find($item['product_id'])
                        ->stockIn($item['quantity'], $item['unit_cost'], 'purchase', $purchase, 'Purchase ' . $purchase->invoice_no);
                }
            }

            return $purchase;
        });

        ActivityLog::log('purchase_created', 'Created purchase ' . $purchase->invoice_no . ' — ৳' . number_format($purchase->total, 2), $purchase);

        return redirect()->route('admin.purchases.show', $purchase)->with('success', 'Purchase recorded.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'payments']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            // Reverse the stock that this purchase added (FIFO out).
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stockOut($item->quantity, 'adjustment', $purchase, 'Reversal of purchase ' . $purchase->invoice_no);
                    }
                }
            }

            $purchase->delete(); // cascades purchase_items
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase deleted and stock reversed.');
    }

    public function bulkCreate()
    {
        return view('admin.purchases.bulk', [
            'suppliers' => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function importTemplate()
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="purchase-import-template.csv"'];

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Product', 'Quantity', 'Unit Cost']);
            fputcsv($out, ['SKU-1 or barcode or exact name', '10', '500']);
            fclose($out);
        }, 'purchase-import-template.csv', $headers);
    }

    public function bulkStore(Request $request)
    {
        $meta = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', 'in:received,pending'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt', 'xlsx'], true)) {
            return back()->withErrors(['file' => 'Please upload a CSV or XLSX file.']);
        }

        $rows = Spreadsheet::rows($file->getRealPath(), $ext);
        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'The file has no data rows.']);
        }

        // Map headers: Product / Quantity / Unit Cost.
        $header = array_shift($rows);
        $map = [];
        foreach ($header as $idx => $label) {
            $key = $this->normalizeItemHeader((string) $label);
            if ($key) {
                $map[$key] = $idx;
            }
        }
        foreach (['product', 'quantity', 'unit_cost'] as $required) {
            if (! isset($map[$required])) {
                return back()->withErrors(['file' => 'Columns required: Product, Quantity, Unit Cost.']);
            }
        }

        $items = [];
        $errors = [];
        foreach ($rows as $n => $row) {
            $ref = trim((string) ($row[$map['product']] ?? ''));
            $qty = (int) ($row[$map['quantity']] ?? 0);
            $cost = (float) ($row[$map['unit_cost']] ?? 0);
            if ($ref === '') {
                continue;
            }

            $product = $this->resolveProduct($ref);
            if (! $product) {
                if (count($errors) < 10) {
                    $errors[] = 'Row ' . ($n + 2) . ": product \"{$ref}\" not found.";
                }
                continue;
            }
            if ($qty < 1) {
                if (count($errors) < 10) {
                    $errors[] = 'Row ' . ($n + 2) . ": invalid quantity for \"{$ref}\".";
                }
                continue;
            }

            $items[] = ['product_id' => $product->id, 'quantity' => $qty, 'unit_cost' => $cost];
        }

        if (empty($items)) {
            return back()->withErrors(['file' => 'No valid items found.'])->withInput();
        }

        $data = $meta + ['items' => $items];

        $purchase = DB::transaction(function () use ($data) {
            $computed = $this->computeTotals($data);

            $purchase = Purchase::create([
                'invoice_no' => $this->nextInvoiceNo(),
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $computed['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'total' => $computed['total'],
                'paid' => $data['paid'] ?? 0,
                'due' => $computed['total'] - ($data['paid'] ?? 0),
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($computed['items'] as $item) {
                $purchase->items()->create($item);
                if ($data['status'] === 'received') {
                    Product::find($item['product_id'])
                        ->stockIn($item['quantity'], $item['unit_cost'], 'purchase', $purchase, 'Purchase ' . $purchase->invoice_no);
                }
            }

            return $purchase;
        });

        return redirect()->route('admin.purchases.show', $purchase)
            ->with('success', count($items) . ' items imported.' . (count($errors) ? ' Some rows were skipped.' : ''))
            ->with('import_errors', $errors);
    }

    private function normalizeItemHeader(string $label): ?string
    {
        $key = strtolower(trim($label));
        $aliases = [
            'product' => 'product', 'item' => 'product', 'sku' => 'product', 'barcode' => 'product',
            'quantity' => 'quantity', 'qty' => 'quantity',
            'unit cost' => 'unit_cost', 'cost' => 'unit_cost', 'price' => 'unit_cost', 'unit price' => 'unit_cost',
        ];

        return $aliases[$key] ?? null;
    }

    private function resolveProduct(string $ref): ?Product
    {
        return Product::where('sku', $ref)
            ->orWhere('barcode', $ref)
            ->orWhere('name', $ref)
            ->first();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', 'in:received,pending'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function computeTotals(array $data): array
    {
        $items = [];
        $subtotal = 0;
        foreach ($data['items'] as $row) {
            $lineSub = $row['quantity'] * $row['unit_cost'];
            $subtotal += $lineSub;
            $items[] = [
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'unit_cost' => $row['unit_cost'],
                'subtotal' => $lineSub,
            ];
        }
        $total = $subtotal - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

        return ['items' => $items, 'subtotal' => $subtotal, 'total' => $total];
    }

    private function nextInvoiceNo(): string
    {
        return 'PUR-' . str_pad((string) (Purchase::max('id') + 1), 5, '0', STR_PAD_LEFT);
    }
}
