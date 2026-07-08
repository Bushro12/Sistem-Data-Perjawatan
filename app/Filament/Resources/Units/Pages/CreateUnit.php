<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    public function getTitle(): string
    {
        return 'Tambah Unit';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
        ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
        ->hidden();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
        ->label('Batal');
    }
}
