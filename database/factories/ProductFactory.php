<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'sku' => 'PRD-'.$this->faker->unique()->numerify('####'),
            'category_id' => Category::factory(),
            'price' => $this->faker->randomFloat(2, 1, 30),
            'cost' => $this->faker->randomFloat(2, 0.5, 10),
            'unit' => 'piece',
            'stock_qty' => 50,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ];
    }
}
