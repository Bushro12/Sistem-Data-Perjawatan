<?php

namespace App\Filament\Resources\Greds\Pages;

use App\Filament\Resources\Greds\GredResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGreds extends ListRecords
{
    protected static string $resource = GredResource::class;

     protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Gred')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Gred')
                ->modalSubmitActionLabel('Tambah')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
