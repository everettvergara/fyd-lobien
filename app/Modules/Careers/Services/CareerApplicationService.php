<?php

namespace App\Modules\Careers\Services;

use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Models\CareerJob;
use Illuminate\Http\Request;

class CareerApplicationService
{
    public function __construct(
        protected CareerApplicationStorageService $storage,
    ) {}

    public function store(CareerJob $job, array $data, Request $request): CareerApplication
    {
        $stored = $this->storage->store($request->file('resume'));

        return CareerApplication::create([
            'career_job_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'remarks' => $data['remarks'] ?? null,
            'resume_path' => $stored['path'],
            'resume_original_filename' => $stored['original_filename'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
