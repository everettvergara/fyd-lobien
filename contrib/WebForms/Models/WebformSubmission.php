<?php

namespace App\Modules\WebForms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebformSubmission extends Model
{
    protected $fillable = [
        'webform_id',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function webform(): BelongsTo
    {
        return $this->belongsTo(Webform::class);
    }

    public function previewText(int $limit = 80): string
    {
        $parts = [];

        foreach ($this->data ?? [] as $value) {
            if (is_bool($value)) {
                $parts[] = $value ? 'Yes' : 'No';
            } elseif (is_scalar($value) && (string) $value !== '') {
                $parts[] = (string) $value;
            }

            if (count($parts) >= 3) {
                break;
            }
        }

        return str(implode(' · ', $parts))->limit($limit)->toString();
    }
}
