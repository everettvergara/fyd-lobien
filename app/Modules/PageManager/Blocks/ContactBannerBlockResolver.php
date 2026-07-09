<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Services\SettingsService;

class ContactBannerBlockResolver implements BlockResolver
{
    private const PHONE_DIRECT = '+632 8983 9311';

    private const DEFAULT_IMAGE = 'https://www.lobiengroup.com/sites/default/files/images/Copy%20of%20Team%20Banner%20%28May%202026%29_0.jpg';

    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $contact = $this->settings->group('contact');

        $phones = array_values(array_filter([
            (string) ($contact['phone'] ?? ''),
            self::PHONE_DIRECT,
        ]));

        return [
            'heading' => (string) ($config['heading'] ?? 'CONTACT US'),
            'description' => (string) ($config['description'] ?? ''),
            'address' => (string) ($contact['address'] ?? ''),
            'phones' => $phones,
            'email' => (string) ($contact['email'] ?? ''),
            'image_url' => (string) ($config['image_url'] ?? self::DEFAULT_IMAGE),
            'show_cta_bar' => filter_var($config['show_cta_bar'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
