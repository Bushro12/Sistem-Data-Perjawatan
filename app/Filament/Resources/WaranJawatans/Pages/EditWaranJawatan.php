<?php

namespace App\Filament\Resources\WaranJawatans\Pages;

use App\Filament\Resources\WaranJawatans\WaranJawatanResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWaranJawatan extends EditRecord
{
    protected static string $resource = WaranJawatanResource::class;

     protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            DeleteAction::make()
            ->label('Padam'),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
