<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAjaxTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['role' => 'cashier', 'department' => null]);
    }

    private function product(int $price = 5, int $stock = 10): Product
    {
        return Product::factory()->create(['price' => $price, 'stock_qty' => $stock]);
    }

    public function test_add_returns_json_cart_html_without_redirect(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $response = $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', $product->name.' added to cart.')
            ->assertJsonStructure(['cart_html', 'held_html']);

        $this->assertEquals([$product->id => 1], session('pos.cart'));
    }

    public function test_add_exceeding_stock_returns_json_error(): void
    {
        $this->actingAs($this->user());
        $product = $this->product(stock: 1);

        $this->postJson('/pos/add', ['product_id' => $product->id]);
        $response = $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not enough stock for '.$product->name.'.');

        $this->assertEquals([$product->id => 1], session('pos.cart'));
    }

    public function test_update_qty_returns_json_and_changes_cart(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response = $this->postJson('/pos/update-qty', ['product_id' => $product->id, 'qty' => 3]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertEquals([$product->id => 3], session('pos.cart'));
    }

    public function test_update_qty_beyond_stock_returns_json_error(): void
    {
        $this->actingAs($this->user());
        $product = $this->product(stock: 2);

        $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response = $this->postJson('/pos/update-qty', ['product_id' => $product->id, 'qty' => 5]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not enough stock for '.$product->name.'.');

        $this->assertEquals([$product->id => 1], session('pos.cart'));
    }

    public function test_update_qty_to_zero_removes_item(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);
        $this->postJson('/pos/update-qty', ['product_id' => $product->id, 'qty' => 0.01]);

        $response = $this->postJson('/pos/remove', ['product_id' => $product->id]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertEmpty(session('pos.cart'));
    }

    public function test_remove_returns_json_and_clears_item(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response = $this->postJson('/pos/remove', ['product_id' => $product->id]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Item removed from cart.');
        $this->assertEmpty(session('pos.cart'));
    }

    public function test_clear_returns_json_and_empties_cart(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response = $this->postJson('/pos/clear');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Cart cleared.');
        $this->assertNull(session('pos.cart'));
    }

    public function test_hold_returns_json_and_moves_cart_to_held(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);

        $response = $this->postJson('/pos/hold');

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertCount(1, session('pos.held_carts'));
        $this->assertNull(session('pos.cart'));
    }

    public function test_hold_empty_cart_returns_json_error(): void
    {
        $this->actingAs($this->user());

        $response = $this->postJson('/pos/hold');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your cart is empty.');
    }

    public function test_resume_returns_json_and_restores_cart(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);
        $this->postJson('/pos/hold');

        $key = array_key_first(session('pos.held_carts'));

        $response = $this->postJson('/pos/resume/'.$key);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertArrayHasKey($product->id, session('pos.cart'));
        $this->assertEmpty(session('pos.held_carts'));
    }

    public function test_resume_missing_hold_returns_json_error(): void
    {
        $this->actingAs($this->user());

        $response = $this->postJson('/pos/resume/missing-key');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_discard_returns_json_and_removes_hold(): void
    {
        $this->actingAs($this->user());
        $product = $this->product();

        $this->postJson('/pos/add', ['product_id' => $product->id]);
        $this->postJson('/pos/hold');

        $key = array_key_first(session('pos.held_carts'));

        $response = $this->postJson('/pos/discard/'.$key);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertEmpty(session('pos.held_carts'));
    }

    public function test_search_returns_products_html_json(): void
    {
        $this->actingAs($this->user());

        $this->product();
        $found = $this->product();
        $found->update(['name' => 'Croissant Special']);

        $response = $this->getJson('/pos/search?search=Croissant');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonStructure(['products_html']);

        $this->assertStringContainsString('Croissant Special', $response->json('products_html'));
    }

    public function test_search_by_category_filters_products(): void
    {
        $this->actingAs($this->user());

        $category = Category::create(['name' => 'Bread']);
        $match = $this->product();
        $match->update(['category_id' => $category->id, 'name' => 'Sourdough']);
        $other = $this->product();
        $other->update(['name' => 'Birthday Cake']);

        $response = $this->getJson('/pos/search?category_id='.$category->id);

        $response->assertOk()->assertJsonPath('count', 1);
        $this->assertStringContainsString('Sourdough', $response->json('products_html'));
        $this->assertStringNotContainsString('Birthday Cake', $response->json('products_html'));
    }

    public function test_pos_page_renders_ajax_containers(): void
    {
        $this->actingAs($this->user());

        $product = $this->product();
        $response = $this->get('/pos');

        $response->assertOk()
            ->assertSee('id="pos-app"', false)
            ->assertSee('id="pos-cart"', false)
            ->assertSee('data-add-product="'.$product->id.'"', false);
    }

    public function test_non_ajax_requests_still_redirect(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $product = $this->product();

        $this->post('/pos/add', ['product_id' => $product->id])->assertRedirect();
        $this->assertArrayHasKey($product->id, session('pos.cart'));
    }
}
