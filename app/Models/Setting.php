<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings');
    }

    /** @return array<string, mixed> */
    public static function allCached(): array
    {
        return Cache::remember('site_settings', 3600, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}
