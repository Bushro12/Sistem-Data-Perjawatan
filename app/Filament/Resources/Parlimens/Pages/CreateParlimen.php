<?php

namespace App\Filament\Resources\Parlimens\Pages;

use App\Filament\Resources\Parlimens\ParlimenResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateParlimen extends CreateRecord
{
    protected static string $resource = ParlimenResource::class;

    public function getTitle(): string
    {
        return 'Tambah Parlimen';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
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
}
