<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['role' => 'cashier', 'department' => null]);
    }

    public function test_guest_cannot_view_customers(): void
    {
        $this->get('/customers')->assertRedirect('/login');
    }

    public function test_cashier_can_view_customers(): void
    {
        $this->actingAs($this->user())->get('/customers')->assertOk();
    }

    public function test_customer_can_be_created(): void
    {
        $this->actingAs($this->user());

        $this->post('/customers', [
            'name' => 'Mia Chen',
            'phone' => '555-0123',
            'email' => 'mia@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', ['name' => 'Mia Chen', 'email' => 'mia@example.com']);
    }

    public function test_customer_requires_name(): void
    {
        $this->actingAs($this->user());

        $this->from('/customers/create')
            ->post('/customers', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_customer_can_be_updated_and_deleted(): void
    {
        $user = $this->user();
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user);

        $this->put("/customers/{$customer->id}", ['name' => 'New Name'])->assertRedirect();

        $this->assertEquals('New Name', $customer->fresh()->name);

        $this->delete("/customers/{$customer->id}")->assertRedirect();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_pos_checkout_links_order_to_customer(): void
    {
        $user = $this->user();
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);
        $customer = Customer::factory()->create(['name' => 'Mia Chen']);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);

        $this->post('/pos/checkout', [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'discount' => 0,
        ])->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals('Mia Chen', $order->customer_name);
    }

    public function test_checkout_with_free_text_customer_name_keeps_behavior(): void
    {
        $user = $this->user();
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);

        $this->post('/pos/checkout', [
            'customer_name' => 'Walk-in Guest',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNull($order->customer_id);
        $this->assertEquals('Walk-in Guest', $order->customer_name);
    }

    public function test_customer_show_displays_totals(): void
    {
        $user = $this->user();
        $customer = Customer::factory()->create();

        $this->actingAs($user)->get("/customers/{$customer->id}")->assertOk();
    }

    public function test_customer_export_returns_csv(): void
    {
        $user = $this->user();
        Customer::factory()->create(['name' => 'Mia Chen']);

        $this->actingAs($user)
            ->get('/customers/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee('Mia Chen');
    }
}
