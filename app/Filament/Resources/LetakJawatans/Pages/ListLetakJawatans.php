<?php

namespace App\Filament\Resources\LetakJawatans\Pages;

use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLetakJawatans extends ListRecords
{
    protected static string $resource = LetakJawatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
