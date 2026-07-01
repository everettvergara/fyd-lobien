<?php

namespace App\Modules\Menus\Requests;

use App\Enums\MenuLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('create', \App\Modules\Menus\Models\Menu::class); }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', Rule::enum(MenuLocation::class)],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required_with:items', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:500'],
            'items.*.link_type' => ['nullable', 'in:internal,external'],
            'items.*.target' => ['nullable', 'in:_self,_blank'],
        ];
    }
}
