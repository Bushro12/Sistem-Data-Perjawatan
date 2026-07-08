<?php

namespace App\Filament\Resources\Bahagians\Pages;

use App\Filament\Resources\Bahagians\BahagianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListBahagians extends ListRecords
{
    protected static string $resource = BahagianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Bahagian')
            // ->modal()
            // ->createAnother(false),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
