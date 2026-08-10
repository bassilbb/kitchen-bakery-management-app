<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => null,
        ]);
    }

    public function test_guest_cannot_view_expenses(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
    }

    public function test_staff_cannot_view_expenses(): void
    {
        foreach (['kitchen', 'bakery'] as $department) {
            $user = User::factory()->create(['role' => User::ROLE_STAFF, 'department' => $department]);
            $this->actingAs($user)->get('/expenses')->assertForbidden();
        }
    }

    public function test_admin_can_create_expense(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/expenses', [
                'title' => 'Electricity bill',
                'category' => 'utilities',
                'amount' => 150.50,
                'expense_date' => now()->format('Y-m-d'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'title' => 'Electricity bill',
            'category' => 'utilities',
            'amount' => 150.50,
            'user_id' => $admin->id,
        ]);
    }

    public function test_expense_requires_valid_fields(): void
    {
        $this->actingAs($this->admin());

        $this->from('/expenses/create')
            ->post('/expenses', [
                'title' => '',
                'category' => 'not-a-category',
                'amount' => 0,
                'expense_date' => 'not-a-date',
            ])
            ->assertSessionHasErrors(['title', 'category', 'amount', 'expense_date']);
    }

    public function test_admin_can_update_and_delete_expense(): void
    {
        $admin = $this->admin();
        $expense = Expense::factory()->create(['title' => 'Old title']);

        $this->actingAs($admin);

        $this->put("/expenses/{$expense->id}", [
            'title' => 'New title',
            'category' => 'rent',
            'amount' => 500,
            'expense_date' => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertEquals('New title', $expense->fresh()->title);

        $this->delete("/expenses/{$expense->id}")->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_reports_include_expenses_and_net_profit(): void
    {
        $admin = $this->admin();

        $order = Order::create([
            'order_number' => 'ORD-NET-'.uniqid(),
            'customer_name' => 'Test',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'payment_method' => 'cash',
            'status' => Order::STATUS_COMPLETED,
            'user_id' => $admin->id,
        ]);

        Expense::create([
            'title' => 'Rent',
            'category' => 'rent',
            'amount' => 30,
            'expense_date' => now()->format('Y-m-d'),
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/reports?from='.now()->format('Y-m-d').'&to='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('100.00')
            ->assertSee('30.00')
            ->assertSee('70.00');
    }

    public function test_reports_export_orders_returns_csv(): void
    {
        $admin = $this->admin();

        $order = Order::create([
            'order_number' => 'ORD-CSV-'.uniqid(),
            'customer_name' => 'Test',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'payment_method' => 'cash',
            'status' => Order::STATUS_COMPLETED,
            'user_id' => $admin->id,
        ]);

        $product = Product::factory()->create(['price' => 100]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        $this->actingAs($admin)
            ->get('/reports/export-orders?from='.now()->format('Y-m-d').'&to='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee($order->order_number)
            ->assertSee('Test Product');
    }
}
