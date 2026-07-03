<?php

namespace App\Modules\Newsletter\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class NewsletterSubscriber extends Model
{
    use Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'newsletter_list_id',
        'email',
        'name',
        'user_id',
        'unsubscribe_token',
        'status',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
