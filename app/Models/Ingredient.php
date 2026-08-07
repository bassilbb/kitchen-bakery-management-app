<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'unit', 'stock_qty', 'cost_per_unit',
        'low_stock_threshold', 'supplier_id',
    ];

    protected function casts(): array
    {
        return [
            'stock_qty' => 'float',
            'cost_per_unit' => 'float',
            'low_stock_threshold' => 'float',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements()
    {
        return $this->hasMany(IngredientMovement::class);
    }

    public function recipeProducts()
    {
        return $this->belongsToMany(Product::class, 'recipe_ingredients')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    public function stockValue(): float
    {
        return $this->stock_qty * $this->cost_per_unit;
    }
}
