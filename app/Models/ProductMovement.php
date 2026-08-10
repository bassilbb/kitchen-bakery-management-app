<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMovement extends Model
{
    use HasFactory;

    public const TYPE_PRODUCTION = 'production';
    public const TYPE_SALE = 'sale';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'reference', 'note', 'user_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
