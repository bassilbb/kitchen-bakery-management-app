<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category_id', 'description', 'price', 'cost',
        'unit', 'stock_qty', 'low_stock_threshold', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'cost' => 'float',
            'stock_qty' => 'float',
            'low_stock_threshold' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function movements()
    {
        return $this->hasMany(ProductMovement::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    public function hasRecipe(): bool
    {
        return $this->recipeItems()->exists();
    }
}
