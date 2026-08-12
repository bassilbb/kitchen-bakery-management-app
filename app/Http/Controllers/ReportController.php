<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();

        if ($from->gt($to)) {
            return back()->with('error', 'The "from" date must be before the "to" date.');
        }

        $ordersQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to]);

        $salesTotal = $ordersQuery->sum('total');
        $salesCount = $ordersQuery->count();
        $discountGiven = $ordersQuery->sum('discount');
        $taxCollected = $ordersQuery->sum('tax');

        $paymentBreakdown = Order::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('payment_method')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$from, $to]);

        $expenseTotal = $expenses->sum('amount');
        $expenseByCategory = (clone $expenses)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->sortByDesc('total');

        $netProfit = $salesTotal - $expenseTotal;

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty, SUM(order_items.line_total) as total_revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->get();

        $dailySales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'day' => Carbon::parse($row->day)->format('Y-m-d'),
                'total' => (float) $row->total,
                'orders' => $row->orders,
            ]);

        $productions = Production::with('product')
            ->whereBetween('produced_at', [$from, $to])
            ->latest('produced_at')
            ->take(100)
            ->get();

        $productionTotal = $productions->sum('total_cost');
        $unitsProduced = $productions->sum('quantity');

        $usageByIngredient = IngredientMovement::whereIn('type', [
            IngredientMovement::TYPE_USAGE,
            IngredientMovement::TYPE_ISSUE,
        ])
            ->whereBetween('created_at', [$from, $to])
            ->with('ingredient')
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn ($group) => [
                'name' => $group->first()->ingredient->name,
                'quantity' => abs($group->sum('quantity')),
                'unit' => $group->first()->ingredient->unit,
            ])
            ->sortByDesc('quantity')
            ->take(10)
            ->values();

        $lowStockIngredients = Ingredient::all()->filter(fn ($i) => $i->isLowStock());
        $lowStockProducts = Product::all()->filter(fn ($p) => $p->isLowStock());

        $inventoryValue = [
            'ingredients' => Ingredient::all()->sum(fn ($i) => $i->stockValue()),
            'products' => Product::all()->sum(fn ($p) => $p->stock_qty * $p->cost),
        ];

        return view('reports.index', compact(
            'from', 'to', 'salesTotal', 'salesCount', 'discountGiven', 'taxCollected',
            'paymentBreakdown', 'expenseTotal', 'expenseByCategory', 'netProfit',
            'topProducts', 'dailySales', 'productions', 'productionTotal', 'unitsProduced',
            'usageByIngredient', 'lowStockIngredients', 'lowStockProducts', 'inventoryValue'
        ));
    }

    public function exportOrders(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();

        $orders = Order::with('items')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, ['Order', 'Date', 'Customer', 'Items', 'Payment', 'Subtotal', 'Discount', 'Tax', 'Total']);

        foreach ($orders as $order) {
            $items = $order->items->map(fn ($item) => $item->quantity.'x '.$item->product_name)->join('; ');

            fputcsv($csv, [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i'),
                $order->customer_name ?: 'Walk-in',
                $items,
                ucfirst($order->payment_method),
                number_format($order->subtotal, 2),
                number_format($order->discount, 2),
                number_format($order->tax, 2),
                number_format($order->total, 2),
            ]);
        }

        rewind($csv);
        $contents = stream_get_contents($csv);
        fclose($csv);

        $filename = 'orders-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return Response::make($contents, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
