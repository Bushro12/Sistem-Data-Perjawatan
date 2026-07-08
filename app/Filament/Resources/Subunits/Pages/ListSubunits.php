<?php

namespace App\Filament\Resources\Subunits\Pages;

use App\Filament\Resources\Subunits\SubunitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubunits extends ListRecords
{
    protected static string $resource = SubunitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Sub Unit')
            // ->modal()
            // ->createAnother(false),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
