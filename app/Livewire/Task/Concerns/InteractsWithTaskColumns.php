<?php

namespace App\Livewire\Task\Concerns;

use App\Models\UserPreference;
use App\Support\Task\TaskTableDefinition;

trait InteractsWithTaskColumns
{
    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);
        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => TaskTableDefinition::PREFERENCE_KEY],
            ['value' => $this->visibleColumns]
        );
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = TaskTableDefinition::DEFAULT_COLUMNS;
        $this->updatedVisibleColumns();
    }

    private function storedColumns(): array
    {
        $stored = UserPreference::query()->where('user_id', auth()->id())
            ->where('key', TaskTableDefinition::PREFERENCE_KEY)->first()?->value;

        return $this->sanitizeColumns(
            is_array($stored) ? $stored : TaskTableDefinition::DEFAULT_COLUMNS
        );
    }

    private function sanitizeColumns(array $columns): array
    {
        $selected = array_values(array_intersect(
            array_keys(TaskTableDefinition::COLUMN_OPTIONS), $columns
        ));
        $selected = array_values(array_diff($selected, ['actions']));
        $selected[] = 'actions';

        return count($selected) > 1 ? $selected : TaskTableDefinition::DEFAULT_COLUMNS;
    }
}
