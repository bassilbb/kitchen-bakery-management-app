<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }

        $products = $query->orderBy('name')->get();
        $cart = session('pos.cart', []);
        $categories = Category::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $heldCarts = $this->heldCarts();

        return view('pos.index', compact('products', 'cart', 'categories', 'customers', 'heldCarts'));
    }

    protected function heldCarts(): array
    {
        $held = session('pos.held_carts', []);

        $items = [];
        foreach ($held as $key => $heldCart) {
            $cartItems = [];
            foreach ($heldCart['cart'] as $id => $qty) {
                $product = Product::find($id);
                if ($product) {
                    $cartItems[] = [
                        'name' => $product->name,
                        'qty' => $qty,
                        'line' => round($product->price * $qty, 2),
                    ];
                }
            }

            $items[] = [
                'key' => $key,
                'label' => $heldCart['label'],
                'held_at' => $heldCart['held_at'],
                'items' => $cartItems,
                'total' => round(array_sum(array_column($cartItems, 'line')), 2),
            ];
        }

        return $items;
    }

    protected function cartWithProducts(): array
    {
        $cart = session('pos.cart', []);

        $items = [];
        foreach ($cart as $id => $qty) {
            $product = Product::find($id);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'qty' => (float) $qty,
                    'line_total' => round($product->price * $qty, 2),
                ];
            }
        }

        return $items;
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session('pos.cart', []);
        $current = $cart[$product->id] ?? 0;
        $newQty = $current + 1;

        if ($product->stock_qty < $newQty) {
            return back()->with('error', 'Not enough stock for '.$product->name.'.');
        }

        $cart[$product->id] = $newQty;
        session(['pos.cart' => $cart]);

        return back();
    }

    public function updateQty(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty = (float) $request->qty;

        if ($product->stock_qty < $qty) {
            return back()->with('error', 'Not enough stock for '.$product->name.'.');
        }

        $cart = session('pos.cart', []);

        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $qty;
        }

        session(['pos.cart' => $cart]);

        return back();
    }

    public function remove(Request $request)
    {
        $cart = session('pos.cart', []);
        unset($cart[$request->product_id]);
        session(['pos.cart' => $cart]);

        return back();
    }

    public function clear()
    {
        session()->forget('pos.cart');

        return back();
    }

    public function hold()
    {
        $cart = session('pos.cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $held = session('pos.held_carts', []);
        $held[(string) Str::uuid()] = [
            'cart' => $cart,
            'label' => 'Held '.now()->format('h:i A'),
            'held_at' => now()->toDateTimeString(),
        ];

        session(['pos.held_carts' => $held]);
        session()->forget('pos.cart');

        return back()->with('success', 'Sale put on hold.');
    }

    public function resume(Request $request, string $key)
    {
        $held = session('pos.held_carts', []);

        if (! isset($held[$key])) {
            return back()->with('error', 'That held sale no longer exists.');
        }

        foreach ($held[$key]['cart'] as $id => $qty) {
            $product = Product::find($id);
            if ($product && $product->stock_qty < $qty) {
                return back()->with('error', 'Not enough stock for '.$product->name.'. Adjust quantities after resuming.');
            }
        }

        session(['pos.cart' => $held[$key]['cart']]);
        unset($held[$key]);
        session(['pos.held_carts' => $held]);

        return redirect()->route('pos.index')->with('success', 'Held sale resumed.');
    }

    public function discard(Request $request, string $key)
    {
        $held = session('pos.held_carts', []);

        if (isset($held[$key])) {
            unset($held[$key]);
            session(['pos.held_carts' => $held]);
        }

        return back()->with('success', 'Held sale discarded.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,card,online'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cart = session('pos.cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $items = [];
        foreach ($cart as $id => $qty) {
            $product = Product::find($id);
            if (! $product) {
                continue;
            }
            if ($product->stock_qty < $qty) {
                return back()->with('error', 'Not enough stock for '.$product->name.'.');
            }
            $items[] = [
                'product' => $product,
                'qty' => (float) $qty,
                'line_total' => round($product->price * $qty, 2),
            ];
        }

        if (empty($items)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;

        $subtotal = round(array_sum(array_column($items, 'line_total')), 2);
        $discount = min((float) $request->discount, $subtotal);
        $tax = round(($subtotal - $discount) * (config('pos.tax_rate', 0) / 100), 2);
        $total = round($subtotal - $discount + $tax, 2);

        $order = DB::transaction(function () use ($items, $subtotal, $discount, $tax, $total, $request, $customer) {
            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_id' => $customer?->id,
                'customer_name' => $request->customer_name ?: $customer?->name,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'status' => Order::STATUS_COMPLETED,
                'user_id' => auth()->id(),
                'note' => $request->note,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['product']->price,
                    'line_total' => $item['line_total'],
                ]);

                $item['product']->stock_qty -= $item['qty'];
                $item['product']->save();

                ProductMovement::create([
                    'product_id' => $item['product']->id,
                    'type' => ProductMovement::TYPE_SALE,
                    'quantity' => -$item['qty'],
                    'reference' => $order->order_number,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return $order;
        });

        session()->forget('pos.cart');

        return redirect()->route('pos.show', $order)->with('success', 'Order completed successfully.');
    }

    protected function nextOrderNumber(): string
    {
        return 'ORD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
    }

    public function show(Order $order)
    {
        $order->load('items', 'user');

        return view('pos.show', compact('order'));
    }
}
