<?php

namespace App\Livewire\Project;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexProject extends Component
{
    public bool $active = false;

    public function mount(): void
    {
        $this->active = (bool) auth()->user()?->is_active;
    }

    public function render(): View
    {
        return view('livewire.project.index-project')
            ->layout('layouts.app');
    }
}
