<?php

namespace App\Filament\Resources\ProjectRateSettings\Pages;

use App\Filament\Resources\ProjectRateSettings\ProjectRateSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditProjectRateSetting extends EditRecord
{
    protected static string $resource = ProjectRateSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
