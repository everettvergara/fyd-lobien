<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Models\NewsletterList;

class NewsletterPublicService
{
    public function findActiveBySlug(string $slug): ?NewsletterList
    {
        return NewsletterList::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array{slug: string, name: string, description: string}
     */
    public function blockProps(string $slug): array
    {
        if ($slug === '') {
            return [
                'slug' => '',
                'name' => '',
                'description' => '',
            ];
        }

        $list = $this->findActiveBySlug($slug);

        if ($list === null) {
            return [
                'slug' => $slug,
                'name' => '',
                'description' => '',
            ];
        }

        return [
            'slug' => $list->slug,
            'name' => (string) $list->name,
            'description' => (string) ($list->description ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicDto(NewsletterList $list): array
    {
        $settings = $list->settings();
        $user = auth()->user();
        $email = $user?->email;
        $subscribed = $email !== null && app(NewsletterSubscriptionService::class)
            ->isSubscribed($list, $email);

        return [
            'slug' => $list->slug,
            'name' => $list->name,
            'description' => $list->description,
            'settings' => [
                'subscribe_label' => $settings['subscribe_label'],
                'unsubscribe_label' => $settings['unsubscribe_label'],
                'success_subscribe' => $settings['success_subscribe'],
                'success_unsubscribe' => $settings['success_unsubscribe'],
                'placeholder_email' => $settings['placeholder_email'],
                'fields' => $list->fieldSettings(),
            ],
            'auth' => [
                'logged_in' => auth()->check(),
                'email' => $email,
                'name' => $user?->name,
                'subscribed' => $subscribed,
            ],
        ];
    }
}
