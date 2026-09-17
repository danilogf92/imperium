<?php

namespace App\Livewire\Data\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\UserPreference;
use App\Support\Data\DataTableDefinition;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait InteractsWithDataColumns
{
    public string $selectedColumnView = '';
    public string $columnViewName = '';

    #[Computed]
    public function savedColumnViews(): array
    {
        return auth()->user()?->preferences()
            ->where('key', $this->columnViewsKey())->first()?->value ?? [];
    }

    public function saveColumnView(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::View);
        $this->columnViewName = trim($this->columnViewName);
        $this->validate(['columnViewName' => ['required', 'string', 'max:60']]);

        $views = $this->savedColumnViews;
        foreach ($views as $view) {
            if (mb_strtolower($view['name']) === mb_strtolower($this->columnViewName)) {
                $this->addError('columnViewName', __('A view with this name already exists.'));
                return;
            }
        }

        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);
        $id = (string) Str::uuid();
        $views[$id] = ['name' => $this->columnViewName, 'columns' => $this->visibleColumns];
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => $this->columnViewsKey()],
            ['value' => $views]
        );
        unset($this->savedColumnViews);
        $this->selectedColumnView = $id;
        $this->columnViewName = '';
        $this->saveVisibleColumnsPreference();
        $this->dispatch('column-view-saved');
    }

    public function deleteColumnView(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::View);
        $views = $this->savedColumnViews;
        if (! isset($views[$this->selectedColumnView])) {
            $this->selectedColumnView = '';
            return;
        }

        unset($views[$this->selectedColumnView]);
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => $this->columnViewsKey()],
            ['value' => $views]
        );
        unset($this->savedColumnViews);
        $this->selectedColumnView = '';
        $this->resetValidation('columnViewName');
        $this->dispatch('column-view-deleted');
    }

    public function updatedSelectedColumnView(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::View);
        $view = $this->savedColumnViews[$this->selectedColumnView] ?? null;
        if (! $view) {
            $this->selectedColumnView = '';
            return;
        }

        $this->visibleColumns = $this->sanitizeColumns($view['columns']);
        $this->saveVisibleColumnsPreference();
    }

    public function updatedVisibleColumns(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::View);
        $this->selectedColumnView = '';
        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);
        $this->saveVisibleColumnsPreference();
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = DataTableDefinition::DEFAULT_COLUMNS;
        $this->updatedVisibleColumns();
    }

    public function setSortBy(string $column): void
    {
        if ($column === 'actions' || ! array_key_exists($column, DataTableDefinition::COLUMN_OPTIONS)) {
            return;
        }
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    private function storedColumns(): array
    {
        $stored = UserPreference::query()->where('user_id', auth()->id())
            ->where('key', $this->visibleColumnsKey())->first()?->value;
        // Existing preferences supply initial columns until this project is customized.
        $stored ??= UserPreference::query()->where('user_id', auth()->id())
            ->where('key', DataTableDefinition::PREFERENCE_KEY)->first()?->value;
        $columns = $this->sanitizeColumns(is_array($stored) ? $stored : DataTableDefinition::DEFAULT_COLUMNS);
        foreach ($this->savedColumnViews as $id => $view) {
            if ($this->sanitizeColumns($view['columns']) === $columns) {
                $this->selectedColumnView = (string) $id;
                break;
            }
        }
        return $columns;
    }

    private function saveVisibleColumnsPreference(): void
    {
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => $this->visibleColumnsKey()],
            ['value' => $this->visibleColumns]
        );
    }

    private function columnViewsKey(): string
    {
        return 'projects.data.column_views.v1.'.$this->project->getKey();
    }

    private function visibleColumnsKey(): string
    {
        return 'projects.data.visible_columns.v1.'.$this->project->getKey();
    }

    private function sanitizeColumns(array $columns): array
    {
        $selected = array_values(array_intersect(array_keys(DataTableDefinition::COLUMN_OPTIONS), $columns));
        $selected = array_values(array_diff($selected, ['actions']));
        $selected[] = 'actions';
        return count($selected) > 1 ? $selected : DataTableDefinition::DEFAULT_COLUMNS;
    }

    private function sanitizeSort(): void
    {
        if ($this->sortBy === 'actions' || ! array_key_exists($this->sortBy, DataTableDefinition::COLUMN_OPTIONS)) {
            $this->sortBy = 'id';
        }
        if (! in_array($this->sortDir, ['asc', 'desc'], true)) {
            $this->sortDir = 'desc';
        }
    }
}
