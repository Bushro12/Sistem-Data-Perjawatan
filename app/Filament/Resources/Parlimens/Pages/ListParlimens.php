<?php

namespace App\Filament\Resources\Parlimens\Pages;

use App\Filament\Resources\Parlimens\ParlimenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParlimens extends ListRecords
{
    protected static string $resource = ParlimenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Parlimen')
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
