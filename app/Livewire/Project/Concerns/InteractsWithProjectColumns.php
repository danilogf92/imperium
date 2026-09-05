<?php

namespace App\Livewire\Project\Concerns;

use App\Models\UserPreference;
use App\Support\Project\ProjectTableDefinition;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait InteractsWithProjectColumns
{
    public string $selectedColumnView = '';

    public string $columnViewName = '';

    private const COLUMN_VIEWS_KEY = 'projects.table.column_views.v1';

    #[Computed]
    public function savedColumnViews(): array
    {
        return auth()->user()?->preferences()
            ->where('key', self::COLUMN_VIEWS_KEY)->first()?->value ?? [];
    }

    public function saveColumnView(): void
    {
        abort_unless(auth()->check(), 403);
        $this->columnViewName = trim($this->columnViewName);
        $this->validate(['columnViewName' => ['required', 'string', 'max:60']]);

        $views = $this->savedColumnViews;
        foreach ($views as $view) {
            if (mb_strtolower($view['name']) === mb_strtolower($this->columnViewName)) {
                $this->addError('columnViewName', __('A view with this name already exists.'));

                return;
            }
        }

        $this->visibleColumns = $this->sanitizeVisibleColumns($this->visibleColumns);
        $id = (string) Str::uuid();
        $views[$id] = ['name' => $this->columnViewName, 'columns' => $this->visibleColumns];
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => self::COLUMN_VIEWS_KEY],
            ['value' => $views]
        );
        unset($this->savedColumnViews);
        $this->selectedColumnView = $id;
        $this->columnViewName = '';
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
        $this->dispatch('column-view-saved');
    }

    public function deleteColumnView(): void
    {
        abort_unless(auth()->check(), 403);
        $views = $this->savedColumnViews;
        if (! isset($views[$this->selectedColumnView])) {
            $this->selectedColumnView = '';

            return;
        }

        unset($views[$this->selectedColumnView]);
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => self::COLUMN_VIEWS_KEY],
            ['value' => $views]
        );
        unset($this->savedColumnViews);
        $this->selectedColumnView = '';
        $this->resetValidation('columnViewName');
        $this->dispatch('column-view-deleted');
    }

    public function updatedSelectedColumnView(): void
    {
        $view = $this->savedColumnViews[$this->selectedColumnView] ?? null;
        if (! $view) {
            $this->selectedColumnView = '';

            return;
        }

        $this->visibleColumns = $this->sanitizeVisibleColumns($view['columns']);
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    public function updatedVisibleColumns(): void
    {
        $this->selectedColumnView = '';
        $this->visibleColumns = $this->sanitizeVisibleColumns($this->visibleColumns);
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    public function resetColumns(): void
    {
        $this->selectedColumnView = '';
        $this->visibleColumns = ProjectTableDefinition::DEFAULT_COLUMNS;
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    private function storedVisibleColumns(): array
    {
        $stored = auth()->user()?->preferences()
            ->where('key', ProjectTableDefinition::PREFERENCE_KEY)->first()?->value;

        $columns = $this->sanitizeVisibleColumns($stored ?? ProjectTableDefinition::DEFAULT_COLUMNS);
        foreach ($this->savedColumnViews as $id => $view) {
            if ($this->sanitizeVisibleColumns($view['columns']) === $columns) {
                $this->selectedColumnView = (string) $id;
                break;
            }
        }

        return $columns;
    }

    private function saveVisibleColumnsPreference(): void
    {
        if (! $user = auth()->user()) {
            return;
        }

        UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => ProjectTableDefinition::PREFERENCE_KEY],
            ['value' => $this->visibleColumns]
        );
    }

    private function sanitizeVisibleColumns(mixed $columns): array
    {
        $columns = array_values(array_intersect(
            array_keys(ProjectTableDefinition::COLUMN_OPTIONS), (array) $columns
        ));
        $columns = array_values(array_diff($columns, ['actions']));
        $columns[] = 'actions';

        return count($columns) > 1 ? $columns : ProjectTableDefinition::DEFAULT_COLUMNS;
    }
}
