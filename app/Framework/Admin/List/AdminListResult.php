<?php

namespace App\Framework\Admin\List;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminListResult
{
    public function __construct(
        public readonly AdminListDefinition $definition,
        public readonly AdminListState $state,
        public readonly LengthAwarePaginator $records,
    ) {}
}
