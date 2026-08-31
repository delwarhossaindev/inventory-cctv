<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage categories');
    }

    public function index(Request $request)
    {
        $query = Unit::orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('short_name', 'like', '%' . $request->q . '%');
        }

        return view('admin.units.index', [
            'units' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('admin.units.create', ['unit' => new Unit()]);
    }

    public function store(Request $request)
    {
        Unit::create($this->validateData($request));

        return redirect()->route('admin.units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $unit->update($this->validateData($request, $unit->id));

        return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:units,name' . ($ignoreId ? ',' . $ignoreId : '')],
            'short_name' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
