<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function bakery(): User
    {
        return User::factory()->create(['role' => 'staff', 'department' => 'bakery']);
    }

    private function kitchen(): User
    {
        return User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'department' => null]);
    }

    private function productWithRecipe(int $ingredientStock = 10): array
    {
        $ingredient = Ingredient::factory()->create(['stock_qty' => $ingredientStock, 'cost_per_unit' => 2]);
        $product = Product::factory()->create(['cost' => 1, 'stock_qty' => 0, 'unit' => 'piece']);
        $product->recipeItems()->create(['ingredient_id' => $ingredient->id, 'quantity' => 0.5]);

        return [$ingredient, $product];
    }

    public function test_bakery_can_create_and_submit_request(): void
    {
        [$ingredient, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ])->assertRedirect();

        $requestRecord = ProductionRequest::first();
        $this->assertNotNull($requestRecord);
        $this->assertEquals('submitted', $requestRecord->status);
        $this->assertEquals(2, $requestRecord->items()->first()->required_qty);
        $this->assertEquals(10, $ingredient->fresh()->stock_qty);
    }

    public function test_bakery_can_save_request_as_draft(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'save',
        ])->assertRedirect();

        $this->assertEquals('draft', ProductionRequest::first()->status);
    }

    public function test_request_blocks_when_stock_insufficient(): void
    {
        [, $product] = $this->productWithRecipe(ingredientStock: 1);

        $this->actingAs($this->bakery());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ])->assertSessionHas('error');

        $this->assertNull(ProductionRequest::first());
    }

    public function test_admin_can_force_request_beyond_available_stock(): void
    {
        [, $product] = $this->productWithRecipe(ingredientStock: 1);

        $this->actingAs($this->admin());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
            'force' => 1,
        ])->assertRedirect();

        $this->assertNotNull(ProductionRequest::first());
    }

    public function test_bakery_can_submit_draft_and_cancel(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 2,
            'action' => 'save',
        ])->assertRedirect();

        $requestRecord = ProductionRequest::first();

        $this->post("/production-requests/{$requestRecord->id}/submit")->assertRedirect();
        $this->assertEquals('submitted', $requestRecord->fresh()->status);

        $this->post("/production-requests/{$requestRecord->id}/cancel")->assertRedirect();
        $this->assertEquals('cancelled', $requestRecord->fresh()->status);
    }

    public function test_kitchen_can_approve_and_reject(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 2,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$requestRecord->id}/approve")->assertRedirect();
        $this->assertEquals('approved', $requestRecord->fresh()->status);

        // A second request that gets rejected.
        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 2,
            'action' => 'submit',
        ]);

        $second = ProductionRequest::latest('id')->first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$second->id}/reject", ['rejection_reason' => 'Not this week'])
            ->assertRedirect();
        $this->assertEquals('rejected', $second->fresh()->status);
    }

    public function test_issue_deducts_stock_and_logs_movement(): void
    {
        [$ingredient, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$requestRecord->id}/approve")->assertRedirect();

        $item = $requestRecord->items()->first();

        $this->post("/production-requests/{$requestRecord->id}/issue", [
            'issued' => [$item->id => 2],
        ])->assertRedirect();

        $requestRecord->refresh();
        $this->assertEquals('issued', $requestRecord->status);
        $this->assertEquals(8, $ingredient->fresh()->stock_qty);
        $this->assertDatabaseHas('ingredient_movements', [
            'ingredient_id' => $ingredient->id,
            'type' => IngredientMovement::TYPE_ISSUE,
            'quantity' => -2,
            'reference' => $requestRecord->request_number,
        ]);
    }

    public function test_partial_issue_marks_request_partially_issued(): void
    {
        [$ingredient, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$requestRecord->id}/approve")->assertRedirect();

        $item = $requestRecord->items()->first();

        $this->post("/production-requests/{$requestRecord->id}/issue", [
            'issued' => [$item->id => 1],
        ])->assertRedirect();

        $this->assertEquals('partially_issued', $requestRecord->fresh()->status);
        $this->assertEquals(9, $ingredient->fresh()->stock_qty);
    }

    public function test_cannot_issue_more_than_available(): void
    {
        [, $product] = $this->productWithRecipe(ingredientStock: 3);

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$requestRecord->id}/approve")->assertRedirect();

        $item = $requestRecord->items()->first();

        $this->post("/production-requests/{$requestRecord->id}/issue", [
            'issued' => [$item->id => 10],
        ])->assertSessionHasErrors();

        $this->assertEquals('approved', $requestRecord->fresh()->status);
    }

    public function test_bakery_records_production_after_issuance(): void
    {
        [$ingredient, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->actingAs($this->kitchen());
        $this->post("/production-requests/{$requestRecord->id}/approve")->assertRedirect();

        $item = $requestRecord->items()->first();
        $this->post("/production-requests/{$requestRecord->id}/issue", [
            'issued' => [$item->id => 2],
        ])->assertRedirect();

        $this->actingAs($this->bakery());
        $this->post("/production-requests/{$requestRecord->id}/produce", [
            'quantity' => 4,
            'wastage' => 1,
        ])->assertRedirect();

        $requestRecord->refresh();
        $this->assertEquals('completed', $requestRecord->status);
        $this->assertEquals(4, $product->fresh()->stock_qty);
        $this->assertNotNull($requestRecord->production);
        $this->assertEquals(1, $requestRecord->production->wastage);
        $this->assertDatabaseHas('product_movements', [
            'product_id' => $product->id,
            'type' => 'production',
            'quantity' => 4,
        ]);
    }

    public function test_kitchen_cannot_create_request_or_produce(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->kitchen());

        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ])->assertForbidden();

        $this->get('/production-requests/create')->assertForbidden();
    }

    public function test_bakery_cannot_approve_or_issue(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $requestRecord = ProductionRequest::first();

        $this->post("/production-requests/{$requestRecord->id}/approve")->assertForbidden();
        $this->post("/production-requests/{$requestRecord->id}/issue", ['issued' => []])->assertForbidden();
    }

    public function test_cashier_and_unauth_users_cannot_access(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'department' => null]);
        $this->actingAs($cashier);
        $this->get('/production-requests')->assertForbidden();
    }

    public function test_index_lists_requests_with_status_filter(): void
    {
        [, $product] = $this->productWithRecipe();

        $this->actingAs($this->bakery());
        $this->post('/production-requests', [
            'product_id' => $product->id,
            'quantity' => 4,
            'action' => 'submit',
        ]);

        $this->actingAs($this->kitchen());

        $this->get('/production-requests')->assertOk()->assertSee('submitted');
        $this->get('/production-requests?status=submitted')->assertOk();
        $this->get('/production-requests?status=issued')->assertOk();
    }
}
