<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePencen extends CreateRecord
{
    protected static string $resource = PencenResource::class;

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->hidden();
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    public function getBreadcrumb() : string
    {
        return ('Tambah');
    }

    public function getTitle() : string
    {
        return ('Tambah Penamatan Perkhidmatan');
    }
}
