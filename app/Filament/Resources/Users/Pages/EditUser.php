<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            (int) $this->record->getKey() === (int) auth()->id()
            && array_key_exists('is_active', $data)
            && ! (bool) $data['is_active']
        ) {
            throw ValidationException::withMessages([
                'data.is_active' => 'You cannot disable your own account. Another administrator must do it.',
            ]);
        }

        if (
            (int) $this->record->getKey() === (int) auth()->id()
            && array_key_exists('can_access_admin', $data)
            && (bool) $data['can_access_admin'] !== (bool) $this->record->can_access_admin
        ) {
            throw ValidationException::withMessages([
                'data.can_access_admin' => 'You cannot change your own admin access. Another administrator must do it.',
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
