<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

abstract class PaginatedComponent extends Component
{
    use WithPagination;

    public function paginationView(): string
    {
        return 'components.pagination';
    }
}