<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Sale;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:access pos');
    }

    public function index()
    {
        $registers = CashRegister::with('user')->latest()->paginate(20);
        $current = CashRegister::where('user_id', auth()->id())->whereNull('closed_at')->first();

        return view('admin.cash-register.index', compact('registers', 'current'));
    }

    public function open(Request $request)
    {
        $data = $request->validate(['opening_balance' => 'required|numeric|min:0']);

        CashRegister::create([
            'user_id' => auth()->id(),
            'opening_balance' => $data['opening_balance'],
            'opened_at' => now(),
        ]);

        return back()->with('success', 'Cash register opened.');
    }

    public function close(Request $request, CashRegister $register)
    {
        $data = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $register->update([
            'closing_balance' => $data['closing_balance'],
            'closed_at' => now(),
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Cash register closed.');
    }
}
