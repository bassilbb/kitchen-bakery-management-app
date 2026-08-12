<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Bakery Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Baker Jane',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department' => 'bakery',
            ]
        );

        $kitchenStaff = User::updateOrCreate(
            ['email' => 'kitchen@example.com'],
            [
                'name' => 'Chef Omar',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department' => 'kitchen',
            ]
        );

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Tally Cashier',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        $categories = collect([
            ['name' => 'Bread', 'description' => 'Freshly baked bread'],
            ['name' => 'Pastry', 'description' => 'Flaky pastries and croissants'],
            ['name' => 'Cake', 'description' => 'Cakes and celebration bakes'],
            ['name' => 'Cookies', 'description' => 'Cookies and biscuits'],
            ['name' => 'Beverages', 'description' => 'Hot and cold drinks'],
        ])->map(fn ($c) => Category::create($c));

        $suppliers = collect([
            ['name' => 'Golden Flour Co.', 'contact_person' => 'Mark Olsen', 'phone' => '555-0101', 'email' => 'sales@goldenflour.test'],
            ['name' => 'Sweet Valley Dairy', 'contact_person' => 'Anna Reyes', 'phone' => '555-0102', 'email' => 'anna@sweetvalley.test'],
            ['name' => 'Cocoa House Imports', 'contact_person' => 'Peter Novak', 'phone' => '555-0103', 'email' => 'peter@cocoahouse.test'],
        ])->map(fn ($s) => Supplier::create($s));

        $ingredientDefs = [
            ['name' => 'Wheat Flour', 'sku' => 'ING-FLR', 'unit' => 'kg', 'stock_qty' => 250, 'cost_per_unit' => 1.2, 'low_stock_threshold' => 10, 'supplier' => 0],
            ['name' => 'Sugar', 'sku' => 'ING-SGR', 'unit' => 'kg', 'stock_qty' => 150, 'cost_per_unit' => 1.5, 'low_stock_threshold' => 5, 'supplier' => 0],
            ['name' => 'Butter', 'sku' => 'ING-BTR', 'unit' => 'kg', 'stock_qty' => 100, 'cost_per_unit' => 5.0, 'low_stock_threshold' => 4, 'supplier' => 1],
            ['name' => 'Eggs', 'sku' => 'ING-EGG', 'unit' => 'piece', 'stock_qty' => 500, 'cost_per_unit' => 0.35, 'low_stock_threshold' => 30, 'supplier' => 1],
            ['name' => 'Milk', 'sku' => 'ING-MLK', 'unit' => 'L', 'stock_qty' => 80, 'cost_per_unit' => 1.1, 'low_stock_threshold' => 5, 'supplier' => 1],
            ['name' => 'Yeast', 'sku' => 'ING-YST', 'unit' => 'g', 'stock_qty' => 2000, 'cost_per_unit' => 0.02, 'low_stock_threshold' => 200, 'supplier' => 0],
            ['name' => 'Cocoa Powder', 'sku' => 'ING-CCO', 'unit' => 'kg', 'stock_qty' => 40, 'cost_per_unit' => 8.0, 'low_stock_threshold' => 2, 'supplier' => 2],
            ['name' => 'Dark Chocolate', 'sku' => 'ING-CHC', 'unit' => 'kg', 'stock_qty' => 30, 'cost_per_unit' => 12.0, 'low_stock_threshold' => 2, 'supplier' => 2],
            ['name' => 'Vanilla Extract', 'sku' => 'ING-VNL', 'unit' => 'ml', 'stock_qty' => 1500, 'cost_per_unit' => 0.06, 'low_stock_threshold' => 100, 'supplier' => 2],
            ['name' => 'Salt', 'sku' => 'ING-SLT', 'unit' => 'kg', 'stock_qty' => 40, 'cost_per_unit' => 0.4, 'low_stock_threshold' => 2, 'supplier' => 0],
            ['name' => 'Baking Powder', 'sku' => 'ING-BKP', 'unit' => 'kg', 'stock_qty' => 25, 'cost_per_unit' => 2.0, 'low_stock_threshold' => 1, 'supplier' => 0],
            ['name' => 'Oats', 'sku' => 'ING-OAT', 'unit' => 'kg', 'stock_qty' => 60, 'cost_per_unit' => 2.2, 'low_stock_threshold' => 3, 'supplier' => 0],
        ];

        $ingredients = collect($ingredientDefs)->map(function ($def) use ($suppliers, $admin) {
            $ing = Ingredient::create([
                'name' => $def['name'],
                'sku' => $def['sku'],
                'unit' => $def['unit'],
                'stock_qty' => $def['stock_qty'],
                'cost_per_unit' => $def['cost_per_unit'],
                'low_stock_threshold' => $def['low_stock_threshold'],
                'supplier_id' => $suppliers[$def['supplier']]->id,
            ]);

            IngredientMovement::create([
                'ingredient_id' => $ing->id,
                'type' => IngredientMovement::TYPE_PURCHASE,
                'quantity' => $def['stock_qty'],
                'unit_cost' => $def['cost_per_unit'],
                'supplier_id' => $ing->supplier_id,
                'reference' => 'Opening stock',
                'user_id' => $admin->id,
                'created_at' => now()->subDays(30),
            ]);

            return $ing;
        });

        $byName = $ingredients->keyBy('name');

        $productDefs = [
            [
                'name' => 'Baguette', 'sku' => 'PRD-BGT', 'category' => 'Bread', 'price' => 3.5,
                'unit' => 'loaf', 'stock_qty' => 12, 'low_stock_threshold' => 5,
                'description' => 'Classic crispy French baguette.',
                'recipe' => ['Wheat Flour' => 0.25, 'Yeast' => 4, 'Salt' => 0.005, 'Water' => 0],
            ],
            [
                'name' => 'Sourdough Loaf', 'sku' => 'PRD-SRD', 'category' => 'Bread', 'price' => 6.0,
                'unit' => 'loaf', 'stock_qty' => 8, 'low_stock_threshold' => 4,
                'description' => 'Slow-fermented tangy sourdough.',
                'recipe' => ['Wheat Flour' => 0.5, 'Yeast' => 2, 'Salt' => 0.01],
            ],
            [
                'name' => 'Croissant', 'sku' => 'PRD-CRS', 'category' => 'Pastry', 'price' => 2.8,
                'unit' => 'piece', 'stock_qty' => 24, 'low_stock_threshold' => 10,
                'description' => 'Buttery, flaky, all-butter croissant.',
                'recipe' => ['Wheat Flour' => 0.06, 'Butter' => 0.04, 'Milk' => 0.03, 'Yeast' => 1, 'Sugar' => 0.01, 'Eggs' => 0.2],
            ],
            [
                'name' => 'Butter Cookies', 'sku' => 'PRD-BCK', 'category' => 'Cookies', 'price' => 1.5,
                'unit' => 'piece', 'stock_qty' => 40, 'low_stock_threshold' => 15,
                'description' => 'Melt-in-the-mouth butter cookies.',
                'recipe' => ['Wheat Flour' => 0.02, 'Butter' => 0.015, 'Sugar' => 0.01, 'Eggs' => 0.1, 'Vanilla Extract' => 0.5],
            ],
            [
                'name' => 'Oat Raisin Cookie', 'sku' => 'PRD-ORC', 'category' => 'Cookies', 'price' => 1.8,
                'unit' => 'piece', 'stock_qty' => 30, 'low_stock_threshold' => 10,
                'description' => 'Chewy oat cookie with raisins.',
                'recipe' => ['Oats' => 0.03, 'Wheat Flour' => 0.01, 'Butter' => 0.015, 'Sugar' => 0.01, 'Eggs' => 0.1],
            ],
            [
                'name' => 'Chocolate Cake', 'sku' => 'PRD-CHK', 'category' => 'Cake', 'price' => 24.0,
                'unit' => 'cake', 'stock_qty' => 4, 'low_stock_threshold' => 2,
                'description' => 'Rich chocolate layer cake with ganache.',
                'recipe' => ['Wheat Flour' => 0.5, 'Sugar' => 0.4, 'Butter' => 0.3, 'Eggs' => 4, 'Milk' => 0.25, 'Cocoa Powder' => 0.1, 'Baking Powder' => 0.02, 'Vanilla Extract' => 5, 'Dark Chocolate' => 0.2],
            ],
            [
                'name' => 'Chocolate Muffin', 'sku' => 'PRD-CHM', 'category' => 'Pastry', 'price' => 2.2,
                'unit' => 'piece', 'stock_qty' => 20, 'low_stock_threshold' => 8,
                'description' => 'Moist chocolate chip muffin.',
                'recipe' => ['Wheat Flour' => 0.05, 'Sugar' => 0.03, 'Butter' => 0.025, 'Eggs' => 0.3, 'Milk' => 0.04, 'Dark Chocolate' => 0.02, 'Baking Powder' => 0.002],
            ],
        ];

        foreach ($productDefs as $def) {
            $product = Product::create([
                'name' => $def['name'],
                'sku' => $def['sku'],
                'category_id' => $categories->firstWhere('name', $def['category'])->id,
                'description' => $def['description'],
                'price' => $def['price'],
                'cost' => 0,
                'unit' => $def['unit'],
                'stock_qty' => $def['stock_qty'],
                'low_stock_threshold' => $def['low_stock_threshold'],
                'is_active' => true,
            ]);

            $cost = 0;
            foreach ($def['recipe'] as $ingName => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $ingredient = $byName->get($ingName);
                if ($ingredient) {
                    $product->recipeItems()->create([
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $qty,
                    ]);
                    $cost += $qty * $ingredient->cost_per_unit;
                }
            }
            $product->update(['cost' => round($cost, 2)]);

            ProductMovement::create([
                'product_id' => $product->id,
                'type' => ProductMovement::TYPE_ADJUSTMENT,
                'quantity' => $def['stock_qty'],
                'reference' => 'Opening stock',
                'user_id' => $admin->id,
                'created_at' => now()->subDays(30),
            ]);
        }

        $this->seedProductionHistory($admin);

        $this->seedOrders($admin, $staff);

        $this->seedCustomers();

        $this->seedExpenses($admin);
    }

    protected function seedProductionHistory(User $admin): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            for ($d = 14; $d >= 0; $d--) {
                $qty = match ($product->unit) {
                    'cake' => rand(2, 4),
                    'loaf' => rand(6, 12),
                    default => rand(12, 30),
                };

                $production = Production::create([
                    'production_number' => 'PRD-'.now()->subDays($d)->format('ymd').'-'.strtoupper(substr(md5($product->id.$d), 0, 5)),
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $product->cost,
                    'total_cost' => round($product->cost * $qty, 2),
                    'user_id' => $admin->id,
                    'produced_at' => now()->subDays($d)->subHours(rand(0, 8)),
                ]);

                foreach ($product->recipeItems as $item) {
                    $needed = $item->quantity * $qty;
                    IngredientMovement::create([
                        'ingredient_id' => $item->ingredient_id,
                        'type' => IngredientMovement::TYPE_USAGE,
                        'quantity' => -$needed,
                        'reference' => $production->production_number,
                        'note' => 'Seeded production',
                        'user_id' => $admin->id,
                        'created_at' => $production->produced_at,
                    ]);
                }

                ProductMovement::create([
                    'product_id' => $product->id,
                    'type' => ProductMovement::TYPE_PRODUCTION,
                    'quantity' => $qty,
                    'reference' => $production->production_number,
                    'user_id' => $admin->id,
                    'created_at' => $production->produced_at,
                ]);

                $product->stock_qty += $qty;
                $product->save();
            }
        }
    }

    protected function seedOrders(User $admin, User $staff): void
    {
        $products = Product::all();
        $customerNames = ['Mia Chen', 'John Doe', 'Sofia Martins', 'Liam Walker', 'Nina Petrova', null, null, 'Oliver King', null, 'Emma Brown'];

        $customerIds = collect($customerNames)
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($name) => [$name => Customer::create(['name' => $name])->id])
            ->all();

        $available = $products->mapWithKeys(fn ($p) => [$p->id => $p->stock_qty])->all();

        for ($d = 14; $d >= 0; $d--) {
            $ordersToday = rand(2, 4);
            for ($i = 0; $i < $ordersToday; $i++) {
                $lineItems = [];
                $itemCount = rand(1, 2);
                foreach ($products->random($itemCount) as $product) {
                    $desired = $product->unit === 'cake' ? rand(1, 2) : rand(1, 2);
                    $qty = (int) min($desired, $available[$product->id] ?? 0);
                    if ($qty > 0) {
                        $lineItems[] = ['product' => $product, 'qty' => $qty];
                    }
                }

                if (empty($lineItems)) {
                    continue;
                }

                $subtotal = round(array_sum(array_map(fn ($l) => $l['product']->price * $l['qty'], $lineItems)), 2);
                $discount = rand(0, 1) ? round($subtotal * rand(5, 15) / 100, 2) : 0;
                $tax = round(($subtotal - $discount) * config('pos.tax_rate', 0) / 100, 2);
                $total = round($subtotal - $discount + $tax, 2);
                $methods = ['cash', 'cash', 'card', 'online'];

                $orderCustomerName = $customerNames[array_rand($customerNames)];

                $order = Order::create([
                    'order_number' => 'ORD-'.now()->subDays($d)->format('ymd').'-'.strtoupper(substr(md5($d.$i), 0, 5)),
                    'customer_id' => $customerIds[$orderCustomerName] ?? null,
                    'customer_name' => $orderCustomerName,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_method' => $methods[array_rand($methods)],
                    'status' => 'completed',
                    'user_id' => $d % 2 ? $staff->id : $admin->id,
                    'created_at' => now()->subDays($d)->setTime(rand(8, 18), rand(0, 59)),
                ]);

                foreach ($lineItems as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'quantity' => $line['qty'],
                        'unit_price' => $line['product']->price,
                        'line_total' => round($line['product']->price * $line['qty'], 2),
                    ]);

                    $available[$line['product']->id] -= $line['qty'];
                    $line['product']->stock_qty -= $line['qty'];
                    $line['product']->save();

                    ProductMovement::create([
                        'product_id' => $line['product']->id,
                        'type' => ProductMovement::TYPE_SALE,
                        'quantity' => -$line['qty'],
                        'reference' => $order->order_number,
                        'user_id' => $order->user_id,
                        'created_at' => $order->created_at,
                    ]);
                }
            }
        }
    }

    protected function seedCustomers(): void
    {
        $names = ['Grace Adeyemi', 'David Okafor', 'Halima Bello', 'Tunde Bakare', 'Amara Nwosu'];

        foreach ($names as $name) {
            Customer::firstOrCreate(
                ['name' => $name],
                [
                    'phone' => '555-0'.rand(100, 999),
                    'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                    'address' => rand(0, 1) ? fake()->streetAddress : null,
                ]
            );
        }
    }

    protected function seedExpenses(User $admin): void
    {
        $defs = [
            ['title' => 'Electricity bill', 'category' => 'utilities', 'daysAgo' => 1, 'amount' => 85.00],
            ['title' => 'Water bill', 'category' => 'utilities', 'daysAgo' => 2, 'amount' => 40.00],
            ['title' => 'Flour delivery - Golden Flour Co.', 'category' => 'ingredients', 'daysAgo' => 3, 'amount' => 320.00],
            ['title' => 'Packaging boxes', 'category' => 'packaging', 'daysAgo' => 4, 'amount' => 75.50],
            ['title' => 'Shop rent', 'category' => 'rent', 'daysAgo' => 5, 'amount' => 600.00],
            ['title' => 'Oven maintenance', 'category' => 'equipment', 'daysAgo' => 7, 'amount' => 120.00],
            ['title' => 'Social media ads', 'category' => 'marketing', 'daysAgo' => 9, 'amount' => 50.00],
            ['title' => 'Bakery staff wages', 'category' => 'wages', 'daysAgo' => 12, 'amount' => 450.00],
        ];

        foreach ($defs as $def) {
            Expense::create([
                'title' => $def['title'],
                'category' => $def['category'],
                'amount' => $def['amount'],
                'expense_date' => now()->subDays($def['daysAgo'])->format('Y-m-d'),
                'user_id' => $admin->id,
            ]);
        }
    }
}
