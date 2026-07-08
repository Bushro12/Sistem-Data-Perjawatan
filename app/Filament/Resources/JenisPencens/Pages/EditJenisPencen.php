<?php

namespace App\Filament\Resources\JenisPencens\Pages;

use App\Filament\Resources\JenisPencens\JenisPencenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJenisPencen extends EditRecord
{
    protected static string $resource = JenisPencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
