<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::where('group', $group)->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        $stored = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );
    }
}
