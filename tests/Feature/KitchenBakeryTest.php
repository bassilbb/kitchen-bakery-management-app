<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenBakeryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_pos_checkout_creates_order_and_deducts_stock(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'department' => null]);
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/add', ['product_id' => $product->id]);

        $this->post('/pos/checkout', [
            'customer_name' => 'Test Customer',
            'payment_method' => 'cash',
            'discount' => 0,
        ])->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(10, $order->total);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->items->first()->quantity);
        $this->assertEquals(8, $product->fresh()->stock_qty);
    }

    public function test_checkout_blocked_when_stock_depleted(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'department' => null]);
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/add', ['product_id' => $product->id]);

        $product->update(['stock_qty' => 1]);

        $this->post('/pos/checkout', [
            'customer_name' => 'Test Customer',
            'payment_method' => 'cash',
        ])->assertSessionHas('error');

        $this->assertNull(Order::latest()->first());
    }

    public function test_production_requires_recipe(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);
        $product = Product::factory()->create();

        $this->actingAs($user);

        $this->post('/productions', [
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertSessionHas('error');
    }

    public function test_production_deducts_ingredients_and_adds_product_stock(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);
        $flour = Ingredient::factory()->create(['stock_qty' => 10, 'cost_per_unit' => 2]);
        $product = Product::factory()->create(['cost' => 1, 'stock_qty' => 0]);
        $product->recipeItems()->create(['ingredient_id' => $flour->id, 'quantity' => 0.5]);

        $this->actingAs($user);

        $this->post('/productions', [
            'product_id' => $product->id,
            'quantity' => 4,
        ])->assertRedirect();

        $this->assertEquals(8, $flour->fresh()->stock_qty);
        $this->assertEquals(4, $product->fresh()->stock_qty);
    }

    public function test_ingredient_purchase_adds_stock(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);
        $supplier = Supplier::factory()->create();
        $ingredient = Ingredient::factory()->create(['stock_qty' => 5, 'supplier_id' => $supplier->id]);

        $this->actingAs($user);

        $this->post("/ingredients/{$ingredient->id}/purchase", [
            'quantity' => 10,
            'unit_cost' => 2.5,
            'supplier_id' => $supplier->id,
        ])->assertRedirect();

        $this->assertEquals(15, $ingredient->fresh()->stock_qty);
    }

    public function test_order_refund_restores_stock(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'department' => null]);
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);
        $order = $this->makeCompletedOrder($user, $product);

        $this->actingAs($user);

        $this->post("/orders/{$order->id}/refund")->assertRedirect();

        $this->assertEquals('refunded', $order->fresh()->status);
        $this->assertEquals(10, $product->fresh()->stock_qty);
    }

    protected function makeCompletedOrder(User $user, Product $product): Order
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-'.uniqid(),
            'customer_name' => 'Test',
            'subtotal' => 10,
            'discount' => 0,
            'tax' => 0,
            'total' => 10,
            'payment_method' => 'cash',
            'status' => Order::STATUS_COMPLETED,
            'user_id' => $user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 5,
            'line_total' => 10,
        ]);

        $product->stock_qty -= 2;
        $product->save();

        return $order;
    }
}
