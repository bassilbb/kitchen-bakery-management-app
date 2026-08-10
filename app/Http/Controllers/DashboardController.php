<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Production;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        $todaySales = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayOrders = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        $weekSales = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total');

        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        $paymentBreakdown = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('payment_method')
            ->get();

        $inventoryValue = Ingredient::all()->sum(fn ($i) => $i->stockValue());

        $productStockValue = Product::all()->sum(fn ($p) => $p->stock_qty * $p->cost);

        $lowStockIngredients = Ingredient::all()->filter(fn ($i) => $i->isLowStock())->take(6);

        $lowStockProducts = Product::all()->filter(fn ($p) => $p->isLowStock())->take(6);

        $recentOrders = Order::with('items', 'user')
            ->latest()
            ->take(8)
            ->get();

        $todayProduction = Production::whereDate('produced_at', $today)->count();

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty, SUM(order_items.line_total) as total_revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Sales for the last 7 days for the chart
        $salesChart = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'total' => (float) Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total'),
            ];
        });

        return view('dashboard.index', compact(
            'todaySales', 'todayOrders', 'weekSales', 'inventoryValue', 'productStockValue',
            'lowStockIngredients', 'lowStockProducts', 'recentOrders', 'topProducts',
            'salesChart', 'todayProduction', 'todayExpenses', 'paymentBreakdown'
        ));
    }
}
