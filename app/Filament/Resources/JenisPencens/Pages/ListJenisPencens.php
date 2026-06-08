<?php

namespace App\Filament\Resources\JenisPencens\Pages;

use App\Filament\Resources\JenisPencens\JenisPencenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJenisPencens extends ListRecords
{
    protected static string $resource = JenisPencenResource::class;

     protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jenis Penamatan Perkhidmatan')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Jenis Penamatan Perkhidmatan')
                ->modalSubmitActionLabel('Tambah')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
