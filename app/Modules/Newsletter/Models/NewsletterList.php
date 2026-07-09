<?php

namespace App\Modules\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterList extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(NewsletterSubscriber::class);
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    /**
     * @return list<string>
     */
    public static function booleanSettingKeys(): array
    {
        return [
            'get_name',
            'require_name',
            'get_mobile_number',
            'require_mobile_number',
            'get_designation',
            'require_designation',
            'get_company',
            'require_company',
        ];
    }

    /**
     * @return list<string>
     */
    public static function stringSettingKeys(): array
    {
        return [
            'subscribe_label',
            'unsubscribe_label',
            'success_subscribe',
            'success_unsubscribe',
            'placeholder_email',
        ];
    }

    public static function defaultSettings(): array
    {
        return [
            'subscribe_label' => 'Subscribe',
            'unsubscribe_label' => 'Unsubscribe',
            'success_subscribe' => 'Thank you for subscribing.',
            'success_unsubscribe' => 'You have been unsubscribed.',
            'placeholder_email' => 'you@example.com',
            'get_name' => false,
            'require_name' => false,
            'get_mobile_number' => false,
            'require_mobile_number' => false,
            'get_designation' => false,
            'require_designation' => false,
            'get_company' => false,
            'require_company' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeSettingsInput(array $input): array
    {
        $normalized = [];

        foreach (self::booleanSettingKeys() as $key) {
            $normalized[$key] = self::parseSettingBoolean($input[$key] ?? false);
        }

        foreach (self::stringSettingKeys() as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];

            if ($value !== null && $value !== '') {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    public static function parseSettingBoolean(mixed $value): bool
    {
        if (is_array($value)) {
            $value = end($value);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array<string, array{enabled: bool, required: bool}>
     */
    public function fieldSettings(): array
    {
        $settings = $this->settings();

        return [
            'name' => [
                'enabled' => self::parseSettingBoolean($settings['get_name'] ?? false),
                'required' => self::parseSettingBoolean($settings['require_name'] ?? false),
            ],
            'mobile_number' => [
                'enabled' => self::parseSettingBoolean($settings['get_mobile_number'] ?? false),
                'required' => self::parseSettingBoolean($settings['require_mobile_number'] ?? false),
            ],
            'designation' => [
                'enabled' => self::parseSettingBoolean($settings['get_designation'] ?? false),
                'required' => self::parseSettingBoolean($settings['require_designation'] ?? false),
            ],
            'company' => [
                'enabled' => self::parseSettingBoolean($settings['get_company'] ?? false),
                'required' => self::parseSettingBoolean($settings['require_company'] ?? false),
            ],
        ];
    }

    public function settings(): array
    {
        return array_merge(self::defaultSettings(), $this->settings ?? []);
    }
}
