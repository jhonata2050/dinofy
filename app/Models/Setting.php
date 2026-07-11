<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label', 'description', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting.{$key}", 300, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        $value = $setting->is_encrypted && $setting->value
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            default => $value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $storeValue = $setting->is_encrypted && $value
                ? Crypt::encryptString($value)
                : $value;

            $setting->update(['value' => $storeValue]);
        }

        Cache::forget("setting.{$key}");
    }

    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->orderBy('id')->get();
    }
}
