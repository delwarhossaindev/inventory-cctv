<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage suppliers');
    }

    public function index(Request $request)
    {
        $query = Supplier::latest();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('company', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }

        return view('admin.suppliers.index', ['suppliers' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.suppliers.create', ['supplier' => new Supplier()]);
    }

    public function store(Request $request)
    {
        Supplier::create($this->validateData($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validateData($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->with('payments')->latest('purchase_date')->get();

        $totals = [
            'purchases' => $purchases->count(),
            'total' => $purchases->sum('total'),
            'paid' => $purchases->sum('paid') + $purchases->flatMap->payments->sum('amount'),
            'due' => $purchases->sum('due'),
        ];

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'totals'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
