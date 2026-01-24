<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    /**
     * Cast JSON value automatically
     */
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Disable mass assignment issues on composite key logic
     */
    public $incrementing = true;

    /**
     * -------------------------
     * Static Helpers
     * -------------------------
     */

    /**
     * Get a setting value
     */
    public static function get(string $group, string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$group}.{$key}", function () use ($group, $key, $default) {
            return static::where('group', $group)
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }

    /**
     * Set / update a setting value
     */
    public static function set(string $group, string $key, $value): self
    {
        $setting = static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$group}.{$key}");

        return $setting;
    }

    /**
     * Get all settings by group (great for frontend bootstrapping)
     */
    public static function group(string $group): array
    {
        return Cache::rememberForever("settings.group.{$group}", function () use ($group) {
            return static::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Clear all cached settings
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
