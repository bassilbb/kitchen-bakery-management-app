<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionRequest extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PARTIALLY_ISSUED = 'partially_issued';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PARTIALLY_ISSUED => 'Partially Issued',
        self::STATUS_ISSUED => 'Issued',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'request_number', 'product_id', 'quantity', 'status', 'note',
        'requested_by', 'approved_by', 'issued_by',
        'approved_at', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(ProductionRequestItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function production()
    {
        return $this->hasOne(Production::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isFullyIssued(): bool
    {
        return $this->items->isNotEmpty()
            && $this->items->every(fn ($item) => $item->issued_qty !== null && $item->issued_qty >= $item->required_qty);
    }

    public function isPartiallyIssued(): bool
    {
        return $this->items->isNotEmpty()
            && $this->items->some(fn ($item) => $item->issued_qty !== null && $item->issued_qty > 0)
            && ! $this->isFullyIssued();
    }

    /**
     * Bakery staff can draft/submit a request for any product and cancel
     * their own draft or submitted requests.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->status === self::STATUS_DRAFT
            && ($user->isAdmin() || $user->isBakery());
    }

    public function canBeSubmittedBy(User $user): bool
    {
        return $this->status === self::STATUS_DRAFT
            && ($user->isAdmin() || $user->isBakery());
    }

    public function canBeCancelledBy(User $user): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED], true)
            && ($user->isAdmin() || $user->isBakery());
    }

    /**
     * Kitchen staff review submitted requests before issuance.
     */
    public function canBeApprovedBy(User $user): bool
    {
        return $this->status === self::STATUS_SUBMITTED
            && ($user->isAdmin() || $user->isKitchen());
    }

    public function canBeRejectedBy(User $user): bool
    {
        return $this->status === self::STATUS_SUBMITTED
            && ($user->isAdmin() || $user->isKitchen());
    }

    /**
     * Kitchen staff issue ingredients once a request is approved (or
     * partially issued and awaiting the rest).
     */
    public function canBeIssuedBy(User $user): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PARTIALLY_ISSUED], true)
            && ($user->isAdmin() || $user->isKitchen());
    }

    /**
     * Bakery staff record the batch once ingredients have been issued.
     */
    public function canBeProducedBy(User $user): bool
    {
        return $this->status === self::STATUS_ISSUED
            && ($user->isAdmin() || $user->isBakery());
    }
}
