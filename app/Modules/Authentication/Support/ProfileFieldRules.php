<?php

namespace App\Modules\Authentication\Support;

use Illuminate\Validation\Rule;

class ProfileFieldRules
{
    public static function rules(): array
    {
        return [
            'avatar_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')),
            ],
            'remove_avatar' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => [
                'nullable',
                'exists:cities,id',
                Rule::exists('cities', 'id')->where(function ($query) {
                    if (request()->filled('province_id')) {
                        $query->where('province_id', request()->input('province_id'));
                    }
                }),
            ],
            'about_me' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public static function prepare(): void
    {
        if (! request()->filled('province_id')) {
            request()->merge(['city_id' => null]);
        }

        request()->merge([
            'remove_avatar' => request()->boolean('remove_avatar'),
        ]);
    }

    public static function attributes(): array
    {
        return [
            'contact_number',
            'province_id',
            'city_id',
            'about_me',
        ];
    }
}
