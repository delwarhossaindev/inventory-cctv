<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // One query for all product aggregates.
        $productAgg = Product::selectRaw(
            'COUNT(*) as total, SUM(CASE WHEN stock_quantity <= alert_quantity THEN 1 ELSE 0 END) as low_stock, SUM(stock_quantity * purchase_price) as stock_value'
        )->first();

        // One query for today's + this month's sales.
        // sale_date is a date cast, so it is stored as "Y-m-d 00:00:00" — comparing it
        // to a bare "Y-m-d" never matches. DATE() strips the time, as the chart below does.
        $salesAgg = Sale::selectRaw(
            'SUM(CASE WHEN DATE(sale_date) = ? THEN total ELSE 0 END) as today_total,'
            . ' SUM(CASE WHEN DATE(sale_date) = ? THEN 1 ELSE 0 END) as today_count,'
            . " SUM(CASE WHEN strftime('%m', sale_date) = ? AND strftime('%Y', sale_date) = ? THEN total ELSE 0 END) as month_total",
            [$today->toDateString(), $today->toDateString(), $today->format('m'), $today->format('Y')]
        )->first();

        $stats = [
            'products' => (int) $productAgg->total,
            'categories' => Category::count(),
            'low_stock' => (int) $productAgg->low_stock,
            'stock_value' => (float) ($productAgg->stock_value ?? 0),
            'today_sales' => (float) ($salesAgg->today_total ?? 0),
            'today_sales_count' => (int) ($salesAgg->today_count ?? 0),
            'month_sales' => (float) ($salesAgg->month_total ?? 0),
            'month_purchases' => Purchase::whereRaw("strftime('%m', purchase_date) = ?", [$today->format('m')])->whereRaw("strftime('%Y', purchase_date) = ?", [$today->format('Y')])->sum('total'),
        ];

        $recentSales = Sale::with('customer')->latest()->take(8)->get();
        $lowStock = Product::lowStock()->orderBy('stock_quantity')->take(8)->get();

        // Last 7 days sales chart data
        $chartDays = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $dailySales = Sale::whereBetween('sale_date', [$chartDays->first(), $chartDays->last()])
            ->selectRaw('DATE(sale_date) as day, SUM(total) as total, COUNT(*) as count')
            ->groupBy('day')->pluck('total', 'day');

        $chartLabels = $chartDays->map(fn ($d) => $d->format('d M'))->values();
        $chartData = $chartDays->map(fn ($d) => (float) ($dailySales[$d->toDateString()] ?? 0))->values();

        // Top 5 categories by sales
        $topCategories = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.main_category_id')
            ->selectRaw('categories.name, SUM(sale_items.subtotal) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $pieLabels = $topCategories->pluck('name')->values();
        $pieData = $topCategories->pluck('total')->map(fn ($v) => (float) $v)->values();

        return view('admin.dashboard', compact(
            'stats', 'recentSales', 'lowStock',
            'chartLabels', 'chartData', 'pieLabels', 'pieData'
        ));
    }
}
