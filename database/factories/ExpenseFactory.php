<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement(array_keys(Expense::CATEGORIES)),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'expense_date' => $this->faker->dateTimeBetween('-30 days')->format('Y-m-d'),
            'note' => $this->faker->optional()->sentence,
            'user_id' => User::factory(),
        ];
    }
}
