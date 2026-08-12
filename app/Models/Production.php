<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_number', 'product_id', 'quantity', 'wastage', 'unit_cost',
        'total_cost', 'note', 'user_id', 'produced_at', 'production_request_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'wastage' => 'float',
            'unit_cost' => 'float',
            'total_cost' => 'float',
            'produced_at' => 'datetime',
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

    public function request()
    {
        return $this->belongsTo(ProductionRequest::class, 'production_request_id');
    }
}
