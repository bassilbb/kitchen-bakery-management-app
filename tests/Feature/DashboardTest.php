<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'department' => null]);
    }

    public function test_dashboard_shows_chart_canvases(): void
    {
        $this->actingAs($this->admin());

        Order::create([
            'order_number' => 'ORD-TEST-0001',
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => 'cash',
            'subtotal' => 50,
            'discount' => 0,
            'tax' => 0,
            'total' => 50,
            'user_id' => $this->admin()->id,
            'created_at' => now(),
        ]);
        Expense::factory()->create(['amount' => 20, 'expense_date' => today()]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('data-chart', false);
        $response->assertSee('Sales vs Expenses', false);
        $response->assertSee('Payment Methods', false);
        $response->assertSee('doughnut', false);
    }

    public function test_dashboard_supports_pending_orders_without_breaking_charts(): void
    {
        $this->actingAs($this->admin());

        Order::create([
            'order_number' => 'ORD-TEST-0002',
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'online',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'user_id' => $this->admin()->id,
            'created_at' => now(),
        ]);

        $this->get('/dashboard')->assertOk();
    }
}
