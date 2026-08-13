<?php

namespace App\Filament\Resources\BrandSettings\Pages;

use App\Filament\Resources\BrandSettings\BrandSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditBrandSetting extends EditRecord
{
    protected static string $resource = BrandSettingResource::class;

    protected function afterSave(): void
    {
        $this->dispatch('brand-settings-updated');
    }
}
