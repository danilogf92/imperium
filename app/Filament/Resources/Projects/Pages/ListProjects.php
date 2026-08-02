<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Imports\ProjectImporter;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ProjectImporter::class)
                ->label('Import projects'),
            CreateAction::make(),
        ];
    }
}
