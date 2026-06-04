<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePencen extends CreateRecord
{
    protected static string $resource = PencenResource::class;

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
