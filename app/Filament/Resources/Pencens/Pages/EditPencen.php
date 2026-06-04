<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\CancelAction;

use Filament\Resources\Pages\EditRecord;

class EditPencen extends EditRecord
{
    protected static string $resource = PencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
        Action::make('cancel')
            ->label('Cancel')
            ->color('gray')
            ->url($this->getResource()::getUrl('index')),
    ];
    }
}
