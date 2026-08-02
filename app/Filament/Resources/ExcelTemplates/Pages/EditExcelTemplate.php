<?php

namespace App\Filament\Resources\ExcelTemplates\Pages;

use App\Filament\Resources\ExcelTemplates\ExcelTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditExcelTemplate extends EditRecord
{
    protected static string $resource = ExcelTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
