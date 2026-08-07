<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientMovement extends Model
{
    use HasFactory;

    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_USAGE = 'usage';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public $timestamps = false;

    protected $fillable = [
        'ingredient_id', 'type', 'quantity', 'unit_cost',
        'supplier_id', 'reference', 'note', 'user_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'unit_cost' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
