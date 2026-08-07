<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    protected $model = \App\Models\Ingredient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'sku' => 'ING-'.$this->faker->unique()->numerify('####'),
            'unit' => 'kg',
            'stock_qty' => 100,
            'cost_per_unit' => $this->faker->randomFloat(2, 1, 10),
            'low_stock_threshold' => 5,
            'supplier_id' => Supplier::factory(),
        ];
    }
}
