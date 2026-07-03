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

    public static function defaultSettings(): array
    {
        return [
            'subscribe_label' => 'Subscribe',
            'unsubscribe_label' => 'Unsubscribe',
            'success_subscribe' => 'Thank you for subscribing.',
            'success_unsubscribe' => 'You have been unsubscribed.',
            'placeholder_email' => 'you@example.com',
        ];
    }

    public function settings(): array
    {
        return array_merge(self::defaultSettings(), $this->settings ?? []);
    }
}
