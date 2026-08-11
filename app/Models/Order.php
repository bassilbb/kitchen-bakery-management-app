<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_METHODS = ['cash', 'card', 'online'];

    protected $fillable = [
        'order_number', 'transaction_reference', 'customer_id', 'customer_name', 'subtotal', 'discount', 'tax', 'total',
        'payment_method', 'status', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'discount' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_REFUNDED], true);
    }
}
