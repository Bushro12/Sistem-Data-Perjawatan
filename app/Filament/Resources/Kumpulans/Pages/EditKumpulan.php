<?php

namespace App\Filament\Resources\Kumpulans\Pages;

use App\Filament\Resources\Kumpulans\KumpulanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKumpulan extends EditRecord
{
    protected static string $resource = KumpulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
