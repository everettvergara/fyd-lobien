<?php

namespace App\Modules\Careers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplication extends Model
{
    protected $fillable = [
        'career_job_id',
        'name',
        'email',
        'contact_number',
        'remarks',
        'resume_path',
        'resume_original_filename',
        'ip_address',
        'user_agent',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(CareerJob::class, 'career_job_id');
    }
}
