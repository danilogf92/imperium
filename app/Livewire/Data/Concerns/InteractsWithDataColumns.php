<?php

namespace App\Livewire\Data\Concerns;

use App\Models\UserPreference;
use App\Support\Data\DataTableDefinition;

trait InteractsWithDataColumns
{
    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeColumns(
            $this->visibleColumns
        );

        UserPreference::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'key' => DataTableDefinition::PREFERENCE_KEY,
            ],
            [
                'value' => $this->visibleColumns,
            ]
        );
    }

    public function resetColumns(): void
    {
        $this->visibleColumns =
            DataTableDefinition::DEFAULT_COLUMNS;

        $this->updatedVisibleColumns();
    }

    public function setSortBy(string $column): void
    {
        if (
            $column === 'actions'
            || ! array_key_exists(
                $column,
                DataTableDefinition::COLUMN_OPTIONS
            )
        ) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir =
                $this->sortDir === 'asc'
                    ? 'desc'
                    : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    private function storedColumns(): array
    {
        $stored = UserPreference::query()
            ->where('user_id', auth()->id())
            ->where(
                'key',
                DataTableDefinition::PREFERENCE_KEY
            )
            ->first()?->value;

        return $this->sanitizeColumns(
            is_array($stored)
                ? $stored
                : DataTableDefinition::DEFAULT_COLUMNS
        );
    }

    private function sanitizeColumns(array $columns): array
    {
        $selected = array_values(
            array_intersect(
                array_keys(
                    DataTableDefinition::COLUMN_OPTIONS
                ),
                $columns
            )
        );

        $selected = array_values(
            array_diff(
                $selected,
                ['actions']
            )
        );

        $selected[] = 'actions';

        return count($selected) > 1
            ? $selected
            : DataTableDefinition::DEFAULT_COLUMNS;
    }

    private function sanitizeSort(): void
    {
        if (
            $this->sortBy === 'actions'
            || ! array_key_exists(
                $this->sortBy,
                DataTableDefinition::COLUMN_OPTIONS
            )
        ) {
            $this->sortBy = 'id';
        }

        if (! in_array(
            $this->sortDir,
            ['asc', 'desc'],
            true
        )) {
            $this->sortDir = 'desc';
        }
    }
}
