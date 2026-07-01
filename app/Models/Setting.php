<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($group, $key, $default);
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        app(SettingsService::class)->set($group, $key, $value, $type);
    }
}
