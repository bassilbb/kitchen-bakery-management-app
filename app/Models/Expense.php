<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'ingredients' => 'Ingredients',
        'packaging' => 'Packaging',
        'utilities' => 'Utilities',
        'rent' => 'Rent',
        'wages' => 'Wages',
        'equipment' => 'Equipment',
        'marketing' => 'Marketing',
        'other' => 'Other',
    ];

    protected $fillable = [
        'title', 'category', 'amount', 'expense_date', 'note', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'expense_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
