<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_request_id', 'ingredient_id', 'required_qty', 'issued_qty',
    ];

    protected function casts(): array
    {
        return [
            'required_qty' => 'float',
            'issued_qty' => 'float',
        ];
    }

    public function request()
    {
        return $this->belongsTo(ProductionRequest::class, 'production_request_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
