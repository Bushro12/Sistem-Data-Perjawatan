<?php

namespace App\Filament\Resources\Kumpulans\Pages;

use App\Filament\Resources\Kumpulans\KumpulanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKumpulans extends ListRecords
{
    protected static string $resource = KumpulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->modal()
            ->createAnother(false),
        ];
    }
}
