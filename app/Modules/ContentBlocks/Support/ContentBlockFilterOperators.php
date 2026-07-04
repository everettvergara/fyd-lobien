<?php

namespace App\Modules\ContentBlocks\Support;

class ContentBlockFilterOperators
{
    /**
     * @return array<string, list<string>>
     */
    public function byFieldType(): array
    {
        return [
            'text' => ['equals', 'not_equals', 'contains', 'starts_with', 'is_empty', 'is_not_empty'],
            'html' => ['contains', 'is_empty', 'is_not_empty'],
            'content_type' => ['in', 'not_in'],
            'date' => ['before', 'after', 'on', 'is_empty'],
            'media' => ['is_empty', 'is_not_empty'],
        ];
    }

    /**
     * @return list<string>
     */
    public function forField(string $field, ContentBlockFieldRegistry $registry): array
    {
        $meta = $registry->meta($field);

        if ($meta === null) {
            return [];
        }

        return $this->byFieldType()[$meta['type']] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'equals' => 'Equals',
            'not_equals' => 'Not equals',
            'contains' => 'Contains',
            'starts_with' => 'Starts with',
            'is_empty' => 'Is empty',
            'is_not_empty' => 'Is not empty',
            'in' => 'Is one of',
            'not_in' => 'Is not one of',
            'before' => 'Before',
            'after' => 'After',
            'on' => 'On',
        ];
    }
}
