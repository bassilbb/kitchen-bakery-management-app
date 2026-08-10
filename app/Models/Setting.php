<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const LOGO_PATH_KEY = 'logo_path';

    public const COMPANY_NAME_KEY = 'company_name';

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function companyName(): string
    {
        return static::get(static::COMPANY_NAME_KEY) ?: config('app.name', 'Kitchen & Bakery Manager');
    }

    public static function logoUrl(): ?string
    {
        $path = static::get(static::LOGO_PATH_KEY);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
