<?php

namespace App\Modules\WebForms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webform extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'schema',
        'is_active',
        'sort_order',
        'public_page_path',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WebformSubmission::class);
    }

    public static function defaultSchema(): array
    {
        return [
            'fields' => [],
            'settings' => [
                'submit_label' => 'Submit',
                'success_message' => 'Thank you for your submission.',
                'redirect_url' => null,
            ],
        ];
    }

    public function fieldDefinitions(): array
    {
        return $this->schema['fields'] ?? [];
    }

    public function settings(): array
    {
        return $this->schema['settings'] ?? Webform::defaultSchema()['settings'];
    }

    public function fieldLabel(string $key): string
    {
        foreach ($this->fieldDefinitions() as $field) {
            if (($field['key'] ?? '') === $key) {
                return (string) ($field['label'] ?? $key);
            }
        }

        return $key;
    }
}
