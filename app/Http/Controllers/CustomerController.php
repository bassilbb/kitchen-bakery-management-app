<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = $this->filtered($request)
            ->withCount(['orders as orders_count' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)], 'total')
            ->orderBy('name')
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $customer->load('orders.items', 'orders.user');

        $orders = $customer->orders()
            ->with('items', 'user')
            ->latest()
            ->paginate(20);

        $totalSpent = $customer->totalSpent();
        $completedOrders = $customer->completedOrdersCount();

        return view('customers.show', compact('customer', 'orders', 'totalSpent', 'completedOrders'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    public function export(Request $request)
    {
        $customers = $this->filtered($request)
            ->withCount(['orders as orders_count' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)], 'total')
            ->orderBy('name')
            ->get();

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, ['Name', 'Phone', 'Email', 'Address', 'Notes', 'Orders', 'Total Spent']);

        foreach ($customers as $customer) {
            fputcsv($csv, [
                $customer->name,
                $customer->phone,
                $customer->email,
                $customer->address,
                $customer->notes,
                (int) ($customer->orders_count ?? 0),
                number_format((float) ($customer->total_spent ?? 0), 2),
            ]);
        }

        rewind($csv);
        $contents = stream_get_contents($csv);
        fclose($csv);

        $filename = 'customers-'.now()->format('Ymd-His').'.csv';

        return Response::make($contents, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function filtered(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        return $query;
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
