<?php

namespace App\Filament\Resources\OpsyenPencens\Pages;

use App\Filament\Resources\OpsyenPencens\OpsyenPencenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpsyenPencens extends ListRecords
{
    protected static string $resource = OpsyenPencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Opsyen Pencem')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Opsyen Pencen')
                ->modalSubmitActionLabel('Tambah')
                ->modalCancelActionLabel('Batal'),
        ];
    }

     public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
