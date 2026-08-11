<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosHoldTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_cart_can_be_held_and_resumed(): void
    {
        $user = $this->user();
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/hold')->assertRedirect();

        $held = session('pos.held_carts');
        $this->assertCount(1, $held);
        $this->assertNull(session('pos.cart'));

        $key = array_key_first($held);

        $this->post("/pos/resume/{$key}")->assertRedirect('/pos');

        $this->assertArrayHasKey($product->id, session('pos.cart'));
        $this->assertEmpty(session('pos.held_carts'));
    }

    public function test_empty_cart_cannot_be_held(): void
    {
        $this->actingAs($this->user());

        $this->post('/pos/hold')->assertSessionHas('error');
    }

    public function test_held_sale_can_be_discarded(): void
    {
        $user = $this->user();
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/hold')->assertRedirect();

        $key = array_key_first(session('pos.held_carts'));

        $this->post("/pos/discard/{$key}")->assertRedirect();

        $this->assertEmpty(session('pos.held_carts'));
    }

    public function test_resuming_missing_hold_returns_error(): void
    {
        $this->actingAs($this->user());

        $this->post('/pos/resume/missing-key')->assertSessionHas('error');
    }

    public function test_resuming_held_sale_checks_stock(): void
    {
        $user = $this->user();
        $product = Product::factory()->create(['price' => 5, 'stock_qty' => 10]);

        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/add', ['product_id' => $product->id]);
        $this->post('/pos/hold')->assertRedirect();

        $product->update(['stock_qty' => 1]);

        $key = array_key_first(session('pos.held_carts'));

        $this->post("/pos/resume/{$key}")->assertSessionHas('error');
        $this->assertNotNull(session('pos.held_carts'));
        $this->assertNull(session('pos.cart'));
    }
}
