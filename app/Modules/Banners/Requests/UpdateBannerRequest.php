<?php

namespace App\Modules\Banners\Requests;

class UpdateBannerRequest extends StoreBannerRequest
{
    public function authorize(): bool { return $this->user()->can('update', $this->route('banner')); }
}
