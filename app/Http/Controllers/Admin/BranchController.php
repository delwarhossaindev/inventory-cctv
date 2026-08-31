<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage settings');
    }

    public function index()
    {
        return view('admin.branches.index', ['branches' => Branch::latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
        ]);

        Branch::create($data);

        return back()->with('success', 'Branch created.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return back()->with('success', 'Branch deleted.');
    }
}
