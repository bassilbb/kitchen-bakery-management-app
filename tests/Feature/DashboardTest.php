<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionRequest;
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

    private function cashier(): User
    {
        return User::factory()->create(['role' => 'cashier', 'department' => null]);
    }

    private function kitchen(): User
    {
        return User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);
    }

    private function bakery(): User
    {
        return User::factory()->create(['role' => 'staff', 'department' => 'bakery']);
    }

    private function completedOrder(string $number, float $total): void
    {
        Order::create([
            'order_number' => $number,
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => 'cash',
            'subtotal' => $total,
            'discount' => 0,
            'tax' => 0,
            'total' => $total,
            'user_id' => $this->admin()->id,
            'created_at' => now(),
        ]);
    }

    public function test_dashboard_shows_chart_canvases(): void
    {
        $this->actingAs($this->admin());

        $this->completedOrder('ORD-TEST-0001', 50);
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

    public function test_cashier_sees_sales_widgets_but_no_kitchen_or_admin_analytics(): void
    {
        $this->actingAs($this->cashier());

        $this->completedOrder('ORD-TEST-0003', 50);
        Expense::factory()->create(['amount' => 20, 'expense_date' => today()]);
        Ingredient::factory()->create(['stock_qty' => 0, 'low_stock_threshold' => 5]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee("Today's Sales", false);
        $response->assertSee("Today's Orders", false);
        $response->assertSee('Top Products');

        $response->assertDontSee("Today's Expenses", false);
        $response->assertDontSee("Today's Net", false);
        $response->assertDontSee('Batches Baked Today');
        $response->assertDontSee('Low Stock Alerts');
        $response->assertDontSee('Sales vs Expenses', false);
    }

    public function test_kitchen_sees_stock_and_request_widgets_only(): void
    {
        $this->actingAs($this->kitchen());

        $ingredient = Ingredient::factory()->create(['stock_qty' => 0, 'low_stock_threshold' => 5]);
        $product = Product::factory()->create();
        $product->recipeItems()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
        ]);

        ProductionRequest::create([
            'request_number' => 'PR-TEST-0001',
            'product_id' => $product->id,
            'quantity' => 1,
            'status' => ProductionRequest::STATUS_SUBMITTED,
            'requested_by' => $this->bakery()->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Low Stock Ingredients');
        $response->assertSee('Awaiting Your Review');

        $response->assertDontSee("Today's Sales", false);
        $response->assertDontSee("Today's Expenses", false);
        $response->assertDontSee('Top Products');
    }

    public function test_bakery_sees_production_widgets_only(): void
    {
        $this->actingAs($this->bakery());

        $product = Product::factory()->create();

        ProductionRequest::create([
            'request_number' => 'PR-TEST-0002',
            'product_id' => $product->id,
            'quantity' => 1,
            'status' => ProductionRequest::STATUS_DRAFT,
            'requested_by' => $this->bakery()->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Batches Baked Today');
        $response->assertSee('Draft Requests');

        $response->assertDontSee("Today's Sales", false);
        $response->assertDontSee("Today's Expenses", false);
        $response->assertDontSee('Top Products');
    }
}
