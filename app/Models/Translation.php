<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }

    public static function getForLocale(string $locale): array
    {
        return Cache::remember("translations_{$locale}", 3600, function () use ($locale) {
            $translations = static::where('locale', $locale)->get();

            $result = [];
            foreach ($translations as $translation) {
                if (!isset($result[$translation->group])) {
                    $result[$translation->group] = [];
                }
                $result[$translation->group][$translation->key] = $translation->value;
            }

            return $result;
        });
    }

    public static function clearCache(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translations_{$locale}");
        } else {
            // Clear all translation caches
            $languages = Language::all();
            foreach ($languages as $language) {
                Cache::forget("translations_{$language->code}");
            }
        }
    }

    public static function importFromFile(string $locale): int
    {
        $path = lang_path("{$locale}/app.php");

        if (!file_exists($path)) {
            return 0;
        }

        $translations = include $path;
        $count = 0;

        foreach ($translations as $group => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    if (is_string($value)) {
                        static::updateOrCreate(
                            [
                                'locale' => $locale,
                                'group' => $group,
                                'key' => $key,
                            ],
                            ['value' => $value]
                        );
                        $count++;
                    }
                }
            }
        }

        static::clearCache($locale);

        return $count;
    }
}
