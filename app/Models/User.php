<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_STAFF = 'staff';

    public const ROLES = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_CASHIER => 'Cashier',
        self::ROLE_STAFF => 'Staff',
    ];

    public const DEPT_KITCHEN = 'kitchen';

    public const DEPT_BAKERY = 'bakery';

    public const DEPARTMENTS = [
        self::DEPT_KITCHEN => 'Kitchen',
        self::DEPT_BAKERY => 'Bakery',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isKitchen(): bool
    {
        return $this->department === self::DEPT_KITCHEN;
    }

    public function isBakery(): bool
    {
        return $this->department === self::DEPT_BAKERY;
    }

    public function canAccessKitchen(): bool
    {
        return $this->isAdmin() || $this->isKitchen();
    }

    public function canAccessBakery(): bool
    {
        return $this->isAdmin() || $this->isBakery();
    }

    /**
     * Cashiers (and admins) are the only users who process sales.
     */
    public function canAccessSales(): bool
    {
        return $this->isAdmin() || $this->isCashier();
    }

    public function roleLabel(): ?string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function departmentLabel(): ?string
    {
        return self::DEPARTMENTS[$this->department] ?? null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
