<?php

namespace App\Support;

class AdminIcon
{
    /**
     * Icons that need an explicit solid target (no -fill twin or better alternative).
     *
     * @var array<string, string>
     */
    private const ALTERNATIVES = [
        'bi-plus-lg' => 'bi-plus-circle-fill',
        'bi-arrow-down-up' => 'bi-arrow-down-up-circle-fill',
        'bi-sort-up' => 'bi-caret-up-fill',
        'bi-sort-down' => 'bi-caret-down-fill',
        'bi-sort-up-alt' => 'bi-caret-up-fill',
        'bi-sort-down-alt' => 'bi-caret-down-fill',
        'bi-box-arrow-up-right' => 'bi-send-fill',
        'bi-list' => 'bi-grid-fill',
        'bi-list-nested' => 'bi-diagram-3-fill',
        'bi-speedometer2' => 'bi-speedometer',
        'bi-folder2-open' => 'bi-folder-fill',
        'bi-journal-text' => 'bi-journal-bookmark-fill',
        'bi-newspaper' => 'bi-megaphone-fill',
        'bi-hourglass-split' => 'bi-pause-circle-fill',
        'bi-clock-history' => 'bi-clock-fill',
        'bi-arrow-repeat' => 'bi-arrow-right-circle-fill',
        'bi-download' => 'bi-cloud-download-fill',
        'bi-files' => 'bi-clipboard-fill',
        'bi-film' => 'bi-camera-video-fill',
        'bi-person-circle' => 'bi-person-fill',
        'bi-table' => 'bi-bar-chart-fill',
        'bi-shield-check' => 'bi-shield-fill-check',
        'bi-shield-x' => 'bi-shield-fill-x',
        'bi-link-45deg' => 'bi-signpost-split-fill',
        'bi-layout-text-window-reverse' => 'bi-file-earmark-richtext-fill',
        'bi-sliders' => 'bi-sliders',
    ];

    public static function solid(?string $icon): string
    {
        if ($icon === null || $icon === '') {
            return 'bi';
        }

        $icon = trim($icon);

        if (! str_starts_with($icon, 'bi-')) {
            $icon = 'bi-'.$icon;
        }

        if (str_ends_with($icon, '-fill')) {
            return 'bi '.$icon;
        }

        if (isset(self::ALTERNATIVES[$icon])) {
            return 'bi '.self::ALTERNATIVES[$icon];
        }

        return 'bi '.$icon.'-fill';
    }
}
