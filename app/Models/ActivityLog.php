<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log) {
            $log->created_at = $log->created_at ?? now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function description(): string
    {
        $userName = $this->user?->name ?? 'System';

        return match ($this->action) {
            'created' => "{$userName} created a {$this->module} record",
            'updated' => "{$userName} updated a {$this->module} record",
            'deleted' => "{$userName} deleted a {$this->module} record",
            'activated' => "{$userName} activated a user account",
            'deactivated' => "{$userName} deactivated a user account",
            'suspended' => "{$userName} suspended a user account",
            'published' => "{$userName} published {$this->module} content",
            default => "{$userName} performed {$this->action} on {$this->module}",
        };
    }
}
