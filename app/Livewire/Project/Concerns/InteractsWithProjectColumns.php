<?php

namespace App\Livewire\Project\Concerns;

use App\Models\UserPreference;
use App\Support\Project\ProjectTableDefinition;

trait InteractsWithProjectColumns
{
    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeVisibleColumns($this->visibleColumns);
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = ProjectTableDefinition::DEFAULT_COLUMNS;
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    private function storedVisibleColumns(): array
    {
        $stored = auth()->user()?->preferences()
            ->where('key', ProjectTableDefinition::PREFERENCE_KEY)->first()?->value;

        return $this->sanitizeVisibleColumns($stored ?? ProjectTableDefinition::DEFAULT_COLUMNS);
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
