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
            ImportAction::make()->icon('heroicon-m-arrow-up-tray')->labeledFrom('sm')
                ->importer(ProjectImporter::class)
                ->label(__('Import projects')),
            CreateAction::make()->icon('heroicon-m-plus')->labeledFrom('sm'),
        ];
    }
}
