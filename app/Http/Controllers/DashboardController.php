<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match (true) {
            $user->isAdmin() => $this->adminDashboard(),
            $user->isCashier() => $this->cashierDashboard(),
            $user->isKitchen() => $this->kitchenDashboard(),
            default => $this->bakeryDashboard(),
        };
    }

    /**
     * Full analytics dashboard - admin only.
     */
    protected function adminDashboard()
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

        $topProducts = $this->topProducts();

        $salesChart = $this->salesChart();

        $netChart = $this->netChart();

        $paymentChart = $this->paymentChart();

        return view('dashboard.index', compact(
            'todaySales', 'todayOrders', 'weekSales', 'inventoryValue', 'productStockValue',
            'lowStockIngredients', 'lowStockProducts', 'recentOrders', 'topProducts',
            'salesChart', 'todayProduction', 'todayExpenses', 'paymentBreakdown', 'netChart', 'paymentChart'
        ));
    }

    /**
     * Sales-focused dashboard - cashiers only.
     */
    protected function cashierDashboard()
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

        $paymentBreakdown = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('payment_method')
            ->get();

        $recentOrders = Order::with('items', 'user')
            ->latest()
            ->take(8)
            ->get();

        $topProducts = $this->topProducts();

        $salesChart = $this->salesChart();

        $paymentChart = $this->paymentChart();

        return view('dashboard.index', compact(
            'todaySales', 'todayOrders', 'weekSales',
            'recentOrders', 'topProducts', 'salesChart', 'paymentChart', 'paymentBreakdown'
        ));
    }

    /**
     * Kitchen dashboard - ingredients, suppliers and request approvals/issuance.
     */
    protected function kitchenDashboard()
    {
        $lowStockIngredients = Ingredient::all()->filter(fn ($i) => $i->isLowStock())->take(6);

        $pendingReviews = ProductionRequest::with('product', 'requester')
            ->where('status', ProductionRequest::STATUS_SUBMITTED)
            ->latest()
            ->take(6)
            ->get();

        $readyToIssue = ProductionRequest::with('product', 'requester')
            ->whereIn('status', [ProductionRequest::STATUS_APPROVED, ProductionRequest::STATUS_PARTIALLY_ISSUED])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.index', compact('lowStockIngredients', 'pendingReviews', 'readyToIssue'));
    }

    /**
     * Bakery dashboard - products, production and request submission/recording.
     */
    protected function bakeryDashboard()
    {
        $today = Carbon::today();

        $lowStockProducts = Product::all()->filter(fn ($p) => $p->isLowStock())->take(6);

        $todayProduction = Production::whereDate('produced_at', $today)->count();

        $draftRequests = ProductionRequest::with('product')
            ->where('status', ProductionRequest::STATUS_DRAFT)
            ->latest()
            ->take(6)
            ->get();

        $readyToProduce = ProductionRequest::with('product')
            ->where('status', ProductionRequest::STATUS_ISSUED)
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.index', compact('lowStockProducts', 'todayProduction', 'draftRequests', 'readyToProduce'));
    }

    protected function topProducts()
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty, SUM(order_items.line_total) as total_revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
    }

    protected function salesChart()
    {
        return collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'total' => (float) Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total'),
            ];
        });
    }

    protected function netChart()
    {
        return collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'sales' => (float) Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total'),
                'expenses' => (float) Expense::whereDate('expense_date', $date)->sum('amount'),
            ];
        });
    }

    protected function paymentChart()
    {
        return Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [
                'method' => $row->payment_method,
                'total' => (float) $row->total,
            ])
            ->values();
    }
}
