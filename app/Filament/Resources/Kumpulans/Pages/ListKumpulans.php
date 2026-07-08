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
                ->label('Tambah Kumpulan')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Kumpulan')
                ->modalSubmitActionLabel('Tambah')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
