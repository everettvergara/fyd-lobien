<?php

namespace App\Modules\Careers\Models;

use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerJob extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /** @var array<int, string> */
    public const EMPLOYMENT_TYPES = [
        'full_time',
        'part_time',
        'contract',
        'internship',
        'remote',
    ];

    protected $fillable = [
        'title',
        'slug',
        'picture_media_id',
        'department',
        'location',
        'salary_range',
        'employment_type',
        'summary',
        'description',
        'requirements',
        'status',
        'published_at',
        'closing_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'closing_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function picture(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'picture_media_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->published()
            ->where(function (Builder $query) {
                $query->whereNull('closing_date')
                    ->orWhereDate('closing_date', '>=', now()->toDateString());
            });
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isOpen(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        if ($this->closing_date === null) {
            return true;
        }

        return $this->closing_date->toDateString() >= now()->toDateString();
    }

    /**
     * @return array<string, string>
     */
    public static function employmentTypeLabels(): array
    {
        return [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'remote' => 'Remote',
        ];
    }

    public function employmentTypeLabel(): string
    {
        return self::employmentTypeLabels()[$this->employment_type] ?? $this->employment_type;
    }
}
