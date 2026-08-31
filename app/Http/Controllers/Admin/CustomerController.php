<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage customers');
    }

    public function index(Request $request)
    {
        $query = Customer::latest();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }

        return view('admin.customers.index', ['customers' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.customers.create', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        Customer::create($this->validateData($request));

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validateData($request));

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->with('payments')->latest('sale_date')->get();

        $totals = [
            'sales' => $sales->count(),
            'total' => $sales->sum('total'),
            'paid' => $sales->sum('paid') + $sales->flatMap->payments->sum('amount'),
            'due' => $sales->sum('due'),
        ];

        return view('admin.customers.show', compact('customer', 'sales', 'totals'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
