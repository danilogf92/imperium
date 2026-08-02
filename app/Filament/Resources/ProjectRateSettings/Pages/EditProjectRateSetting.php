<?php

namespace App\Filament\Resources\ProjectRateSettings\Pages;

use App\Filament\Resources\ProjectRateSettings\ProjectRateSettingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditProjectRateSetting extends EditRecord
{
    protected static string $resource = ProjectRateSettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $minimum = (float) ($data['min_rate'] ?? 0);
        $maximum = (float) ($data['max_rate'] ?? 0);

        if ($minimum >= $maximum) {
            throw ValidationException::withMessages([
                'data.min_rate' => 'The minimum rate must be lower than the maximum rate.',
                'data.max_rate' => 'The maximum rate must be greater than the minimum rate.',
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
