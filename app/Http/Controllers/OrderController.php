<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%'.$request->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$request->search.'%');
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');

        return view('orders.show', compact('order'));
    }

    public function refund(Order $order)
    {
        if ($order->isRefunded()) {
            return back()->with('error', 'This order is already refunded.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => Order::STATUS_REFUNDED]);

            foreach ($order->items as $item) {
                $item->product?->increment('stock_qty', $item->quantity);

                if ($item->product) {
                    ProductMovement::create([
                        'product_id' => $item->product->id,
                        'type' => ProductMovement::TYPE_SALE,
                        'quantity' => $item->quantity,
                        'reference' => $order->order_number.' (refund)',
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Order refunded and stock restored.');
    }
}
