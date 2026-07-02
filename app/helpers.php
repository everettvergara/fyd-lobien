<?php

use App\Support\AdminIcon;

if (! function_exists('admin_icon')) {
    function admin_icon(?string $icon): string
    {
        return AdminIcon::solid($icon);
    }
}
