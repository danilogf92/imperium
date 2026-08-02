<?php

namespace App\Filament\Resources\ExcelTemplates\Pages;

use App\Filament\Resources\ExcelTemplates\ExcelTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExcelTemplates extends ListRecords
{
    protected static string $resource = ExcelTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
