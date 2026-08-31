<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with('mainCategory');

        if ($request->filled('category')) {
            $query->where('main_category_id', $request->category);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function show(Product $product)
    {
        return response()->json($product->load('mainCategory'));
    }

    public function categories()
    {
        return response()->json(Category::whereNull('parent_id')->with('children.children')->get());
    }

    public function checkStock(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'stock_quantity' => $product->stock_quantity,
            'in_stock' => $product->stock_quantity > 0,
            'sale_price' => $product->sale_price,
        ]);
    }
}
