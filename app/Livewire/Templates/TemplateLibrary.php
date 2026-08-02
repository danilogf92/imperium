<?php

namespace App\Livewire\Templates;

use App\Models\ExcelTemplate;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TemplateLibrary extends Component
{
    public function render(): View
    {
        return view('livewire.templates.template-library', [
            'templates' => ExcelTemplate::query()
                ->active()
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
