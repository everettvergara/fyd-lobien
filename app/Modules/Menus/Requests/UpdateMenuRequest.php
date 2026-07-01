<?php

namespace App\Modules\Menus\Requests;

class UpdateMenuRequest extends StoreMenuRequest
{
    public function authorize(): bool { return $this->user()->can('update', $this->route('menu')); }
}
