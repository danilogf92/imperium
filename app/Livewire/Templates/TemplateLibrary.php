<?php

namespace App\Livewire\Templates;

use App\Models\ExcelTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TemplateLibrary extends Component
{
    public string $section = 'all';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function render(): View
    {
        $companies = auth()->user()->availableCompanies();
        if (! in_array($this->section, ['all', 'general', ...$companies->pluck('id')->map(fn ($id) => (string) $id)->all()], true)) {
            $this->section = 'all';
        }

        return view('livewire.templates.template-library', [
            'companies' => $companies,
            'templates' => ExcelTemplate::query()
                ->active()
                ->visibleTo(auth()->user())
                ->with('companies:id,company_name')
                ->when($this->section === 'general', fn (Builder $query) => $query->where('is_global', true))
                ->when(! in_array($this->section, ['all', 'general'], true), fn (Builder $query) => $query
                    ->where('is_global', false)
                    ->whereHas('companies', fn (Builder $companies) => $companies->whereKey((int) $this->section)))
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
