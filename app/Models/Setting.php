<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value configuration that a barangay can edit at runtime.
 *
 * The whole table is cached as a single array, so the dozens of setting()
 * calls inside a print template cost one query per cache miss, not one each.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'type', 'group'];

    public const CACHE_KEY = 'barangay_settings';

    /**
     * Every setting as key => value. Returns [] when the table is not
     * reachable yet (fresh install, mid-migration) so callers fall back
     * to their own defaults instead of throwing.
     */
    public static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function get(string $key, $default = null)
    {
        $value = static::allSettings()[$key] ?? null;

        // Treat an empty stored value as "not configured" so the caller's
        // default still wins — a blank contact number should not blank out
        // a document header.
        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function put(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        static::flushCache();
    }

    /**
     * Bulk write. Only touches keys present in $values.
     */
    public static function putMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted()
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
