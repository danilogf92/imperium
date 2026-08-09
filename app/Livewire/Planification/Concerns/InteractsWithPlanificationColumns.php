<?php

namespace App\Livewire\Planification\Concerns;

use App\Models\UserPreference;

trait InteractsWithPlanificationColumns
{
    public array $visibleColumns = [];

    private const COLUMN_PREFERENCE_KEY = 'planification.fixed_columns.v1';

    private const COLUMN_OPTIONS = [
        'forecast_year' => 'Forecast Start Year', 'plant' => 'Plant', 'pda_code' => 'PDA Code',
        'name' => 'Name', 'budgeted' => 'Budgeted total', 'status' => 'Status',
        'actual_week' => 'Actual Week', 'next_week' => 'Next Week',
    ];

    public function mount(): void
    {
        $this->loadPerPagePreference();
        $stored = auth()->user()?->preferences()
            ->where('key', self::COLUMN_PREFERENCE_KEY)->first()?->value;
        $this->visibleColumns = $this->sanitizeColumns($stored ?? array_keys(self::COLUMN_OPTIONS));
    }

    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);
        $this->saveColumnPreference();
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = array_keys(self::COLUMN_OPTIONS);
        $this->saveColumnPreference();
    }

    private function sanitizeColumns(mixed $columns): array
    {
        $columns = array_values(array_intersect(array_keys(self::COLUMN_OPTIONS), (array) $columns));
        return $columns !== [] ? $columns : array_keys(self::COLUMN_OPTIONS);
    }

    private function saveColumnPreference(): void
    {
        if ($user = auth()->user()) {
            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id, 'key' => self::COLUMN_PREFERENCE_KEY],
                ['value' => $this->visibleColumns]
            );
        }
    }
}
