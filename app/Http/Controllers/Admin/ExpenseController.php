<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage expenses');
    }

    public function index(Request $request)
    {
        $query = Expense::with('category')->latest('expense_date')->latest('id');

        if ($request->filled('category')) {
            $query->where('expense_category_id', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();

        $total = Expense::query()
            ->when($request->filled('category'), fn ($q) => $q->where('expense_category_id', $request->category))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->to))
            ->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'categories', 'total'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'new_category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        if (!empty($data['new_category'])) {
            $cat = ExpenseCategory::firstOrCreate(['name' => $data['new_category']]);
            $data['expense_category_id'] = $cat->id;
        }

        unset($data['new_category']);
        $data['created_by'] = auth()->id();

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense added.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
