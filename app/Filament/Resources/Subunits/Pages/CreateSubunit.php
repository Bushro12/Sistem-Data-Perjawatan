<?php

namespace App\Filament\Resources\Subunits\Pages;

use App\Filament\Resources\Subunits\SubunitResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSubunit extends CreateRecord
{
    protected static string $resource = SubunitResource::class;

    public function getTitle(): string
    {
        return 'Tambah Sub Unit';
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
