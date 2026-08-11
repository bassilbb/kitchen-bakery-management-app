<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['role' => 'cashier', 'department' => null]);
    }

    private function configurePaystack(): void
    {
        Setting::set(Setting::PAYSTACK_PUBLIC_KEY, 'paystack-public-key-for-tests');
        Setting::set(Setting::PAYSTACK_SECRET_KEY, Crypt::encryptString('paystack-secret-key-for-tests'));
    }

    private function product(int $price = 5, int $stock = 10): Product
    {
        return Product::factory()->create(['price' => $price, 'stock_qty' => $stock]);
    }

    private function addToCart(User $user, Product $product, int $qty = 1): void
    {
        $this->actingAs($user);
        $this->post('/pos/add', ['product_id' => $product->id]);

        if ($qty > 1) {
            $this->post('/pos/update-qty', ['product_id' => $product->id, 'qty' => $qty]);
        }
    }

    public function test_online_checkout_creates_pending_order_and_redirects_to_paystack(): void
    {
        $this->configurePaystack();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123'],
            ]),
        ]);

        $user = $this->user();
        $product = $this->product(price: 5);
        $this->addToCart($user, $product, 2);

        $response = $this->post('/pos/checkout', [
            'customer_id' => '',
            'customer_name' => 'Test Buyer',
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        $response->assertRedirect('https://checkout.paystack.com/abc123');

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals('online', $order->payment_method);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertNotNull($order->transaction_reference);
        $this->assertEquals('Test Buyer', $order->customer_name);
        $this->assertEquals(10, $order->total);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->items->first()->quantity);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.paystack.co/transaction/initialize'
                && $request['amount'] === 1000
                && $request['reference'] === Order::first()->transaction_reference;
        });

        // Stock not deducted until callback confirms payment.
        $this->assertEquals(10, $product->fresh()->stock_qty);
    }

    public function test_online_checkout_is_blocked_when_paystack_not_configured(): void
    {
        $this->addToCart($this->user(), $this->product());

        $response = $this->post('/pos/checkout', [
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_online_checkout_fails_gracefully_when_paystack_initialize_fails(): void
    {
        $this->configurePaystack();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Invalid key',
            ], 400),
        ]);

        $this->addToCart($this->user(), $this->product());

        $response = $this->post('/pos/checkout', [
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_FAILED, Order::first()->status);
    }

    public function test_callback_marks_order_completed_and_deducts_stock(): void
    {
        $this->configurePaystack();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/complete'],
            ]),
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'gateway_response' => 'Successful',
                ],
            ]),
        ]);

        $user = $this->user();
        $product = $this->product(price: 5, stock: 10);
        $this->addToCart($user, $product, 2);

        $this->post('/pos/checkout', [
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        $order = Order::first();
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        $response = $this->get('/paystack/callback?reference='.$order->transaction_reference);

        $response->assertRedirect(route('pos.show', $order));

        $order->refresh();
        $this->assertEquals(Order::STATUS_COMPLETED, $order->status);
        $this->assertEquals(8, $product->fresh()->stock_qty);
        $this->assertNull(session('pos.cart'));
        $this->assertDatabaseHas('product_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => -2,
            'reference' => $order->order_number,
        ]);
    }

    public function test_callback_marks_order_failed_and_keeps_cart_when_payment_not_successful(): void
    {
        $this->configurePaystack();

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'failed',
                    'gateway_response' => 'Card declined',
                ],
            ]),
        ]);

        $user = $this->user();
        $product = $this->product(price: 5, stock: 10);
        $this->addToCart($user, $product);

        $this->post('/pos/checkout', [
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        $order = Order::first();

        $response = $this->get('/paystack/callback?reference='.$order->transaction_reference);

        $response->assertRedirect(route('pos.index'));

        $order->refresh();
        $this->assertEquals(Order::STATUS_FAILED, $order->status);
        $this->assertEquals(10, $product->fresh()->stock_qty);
        $this->assertNotNull(session('pos.cart'));
    }

    public function test_callback_without_reference_returns_error(): void
    {
        $this->configurePaystack();
        $this->actingAs($this->user());

        $response = $this->get('/paystack/callback');

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
    }

    public function test_callback_with_unknown_reference_returns_error(): void
    {
        $this->configurePaystack();
        $this->actingAs($this->user());

        $response = $this->get('/paystack/callback?reference=unknown-reference');

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
    }

    public function test_online_checkout_uses_customer_email_when_provided(): void
    {
        $this->configurePaystack();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz'],
            ]),
        ]);

        $user = $this->user();
        $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
        $this->addToCart($user, $this->product());

        $this->post('/pos/checkout', [
            'customer_id' => $customer->id,
            'payment_method' => 'online',
            'discount' => 0,
        ]);

        Http::assertSent(fn ($request) => $request['email'] === 'buyer@example.com');
    }
}
