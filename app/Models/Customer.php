<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'notes',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function completedOrdersCount(): int
    {
        return $this->orders()->where('status', Order::STATUS_COMPLETED)->count();
    }

    public function totalSpent(): float
    {
        return (float) $this->orders()->where('status', Order::STATUS_COMPLETED)->sum('total');
    }
}
