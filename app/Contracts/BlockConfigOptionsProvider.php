<?php

namespace App\Contracts;

interface BlockConfigOptionsProvider
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function options(): array;
}
