<?php

namespace App\Filament\Resources\ExcelTemplates\Pages;

use App\Filament\Resources\ExcelTemplates\ExcelTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExcelTemplate extends CreateRecord
{
    protected static string $resource = ExcelTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
