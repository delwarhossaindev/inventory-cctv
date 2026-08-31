<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Setting;
use App\Support\Pdf;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:access pos');
    }

    public function index()
    {
        $quotations = Quotation::with('customer')->latest()->paginate(20);

        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        return view('admin.quotations.create', [
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(),
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'sale_price']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'quote_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quote_date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        $total = $subtotal - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

        $quotation = Quotation::create([
            'quote_no' => 'QT-' . str_pad((string) (Quotation::max('id') + 1), 5, '0', STR_PAD_LEFT),
            'customer_id' => $data['customer_id'] ?? null,
            'quote_date' => $data['quote_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'subtotal' => $subtotal,
            'discount' => $data['discount'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'total' => $total,
            'status' => 'draft',
            'note' => $data['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('admin.quotations.show', $quotation)->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);
        $settings = Setting::getAll();

        return view('admin.quotations.show', compact('quotation', 'settings'));
    }

    public function pdf(Quotation $quotation, Request $request)
    {
        $quotation->load(['customer', 'items.product']);
        $settings = Setting::getAll();

        return Pdf::render(
            'pdf.quotation',
            compact('quotation', 'settings'),
            'Quotation-' . $quotation->quote_no . '.pdf',
            $request->boolean('download'),
        );
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted.');
    }
}
