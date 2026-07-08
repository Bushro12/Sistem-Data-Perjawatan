<?php

namespace App\Filament\Resources\LetakJawatans\Pages;

use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLetakJawatan extends EditRecord
{
    protected static string $resource = LetakJawatanResource::class;


    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Simpan')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Pengesahan')
            ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
            ->action(fn() => $this->save());
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }


}
