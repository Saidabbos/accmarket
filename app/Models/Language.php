<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'locale', 'code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('active_languages', 3600, function () {
            return static::active()->ordered()->get();
        });
    }

    public static function getDefault(): ?self
    {
        return Cache::remember('default_language', 3600, function () {
            return static::where('is_default', true)->first();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('active_languages');
        Cache::forget('default_language');
    }
}
