<?php

namespace App\Livewire\Concerns;

use App\Models\UserPreference;

trait InteractsWithPerPagePreference
{
    private const PER_PAGE_PREFERENCE_KEY = 'tables.per_page';

    protected function loadPerPagePreference(): void
    {
        $value = auth()->user()?->preferences()
            ->where('key', self::PER_PAGE_PREFERENCE_KEY)->first()?->value;
        $this->perPage = $this->validPerPage(is_array($value) ? ($value['count'] ?? 10) : $value);
    }

    protected function savePerPagePreference(int|string $value): void
    {
        $this->perPage = $this->validPerPage($value);

        if ($user = auth()->user()) {
            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id, 'key' => self::PER_PAGE_PREFERENCE_KEY],
                ['value' => ['count' => $this->perPage]]
            );
        }
    }

    private function validPerPage(mixed $value): int
    {
        $value = (int) $value;

        return in_array($value, [5, 10, 20, 50, 100], true) ? $value : 10;
    }
}
