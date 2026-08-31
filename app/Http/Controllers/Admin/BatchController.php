<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view stock');
    }

    public function index(Request $request)
    {
        $query = StockBatch::with('product')
            ->orderByDesc('received_at')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('batch_no', 'like', "%{$q}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%"));
            });
        }

        $status = $request->get('status', 'in');
        if ($status === 'in') {
            $query->where('remaining', '>', 0);
        } elseif ($status === 'empty') {
            $query->where('remaining', '<=', 0);
        }

        return view('admin.batches.index', [
            'batches' => $query->paginate(30)->withQueryString(),
            'status' => $status,
        ]);
    }
}
