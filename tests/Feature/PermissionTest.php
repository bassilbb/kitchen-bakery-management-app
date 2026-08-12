<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'department' => null]);

        $this->actingAs($admin);

        foreach (['/products', '/productions', '/ingredients', '/suppliers', '/reports', '/users', '/pos', '/orders', '/customers', '/categories', '/expenses', '/settings'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_kitchen_staff_sees_kitchen_only(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);

        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();
        $this->get('/profile')->assertOk();
        $this->get('/ingredients')->assertOk();
        $this->get('/suppliers')->assertOk();

        $this->get('/products')->assertForbidden();
        $this->get('/productions')->assertForbidden();
        $this->get('/categories')->assertForbidden();
        $this->get('/pos')->assertForbidden();
        $this->get('/orders')->assertForbidden();
        $this->get('/customers')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/users')->assertForbidden();
    }

    public function test_bakery_staff_sees_bakery_only(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);

        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();
        $this->get('/profile')->assertOk();
        $this->get('/products')->assertOk();
        $this->get('/productions')->assertOk();
        $this->get('/categories')->assertOk();

        $this->get('/ingredients')->assertForbidden();
        $this->get('/suppliers')->assertForbidden();
        $this->get('/pos')->assertForbidden();
        $this->get('/orders')->assertForbidden();
        $this->get('/customers')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/users')->assertForbidden();
    }

    public function test_cashier_sees_sales_only(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'department' => null]);

        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();
        $this->get('/profile')->assertOk();
        $this->get('/pos')->assertOk();
        $this->get('/orders')->assertOk();
        $this->get('/customers')->assertOk();

        $this->get('/ingredients')->assertForbidden();
        $this->get('/suppliers')->assertForbidden();
        $this->get('/products')->assertForbidden();
        $this->get('/productions')->assertForbidden();
        $this->get('/categories')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/expenses')->assertForbidden();
        $this->get('/settings')->assertForbidden();
    }

    public function test_reports_hidden_from_all_non_admins(): void
    {
        $kitchen = User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);
        $bakery = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);
        $cashier = User::factory()->create(['role' => 'cashier', 'department' => null]);

        foreach ([$kitchen, $bakery, $cashier] as $user) {
            $this->actingAs($user);
            $this->get('/reports')->assertForbidden();
        }
    }

    public function test_admin_can_update_user_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'department' => null]);
        $staff = User::factory()->create(['role' => 'staff', 'department' => 'kitchen']);

        $this->actingAs($admin);

        $this->put("/users/{$staff->id}", [
            'role' => 'staff',
            'department' => 'bakery',
        ])->assertRedirect();

        $this->assertEquals('bakery', $staff->fresh()->department);

        // Promoting to admin clears the department (admin covers everything).
        $this->put("/users/{$staff->id}", [
            'role' => 'admin',
        ])->assertRedirect();

        $this->assertEquals('admin', $staff->fresh()->role);
        $this->assertNull($staff->fresh()->department);
    }

    public function test_admin_can_promote_user_to_cashier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'department' => null]);
        $staff = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);

        $this->actingAs($admin);

        $this->put("/users/{$staff->id}", [
            'role' => 'cashier',
        ])->assertRedirect();

        $this->assertEquals('cashier', $staff->fresh()->role);
        $this->assertNull($staff->fresh()->department);
    }

    public function test_staff_cannot_manage_users(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'department' => 'bakery']);

        $this->actingAs($user);

        $this->get('/users')->assertForbidden();
        $this->put('/users/1', ['role' => 'staff', 'department' => 'kitchen'])->assertForbidden();
    }

    public function test_cashier_cannot_manage_users(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'department' => null]);

        $this->actingAs($cashier);

        $this->get('/users')->assertForbidden();
        $this->put('/users/1', ['role' => 'cashier'])->assertForbidden();
    }
}
