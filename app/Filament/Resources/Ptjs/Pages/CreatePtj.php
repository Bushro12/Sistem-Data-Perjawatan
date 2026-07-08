<?php

namespace App\Filament\Resources\Ptjs\Pages;

use App\Filament\Resources\Ptjs\PtjResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePtj extends CreateRecord
{
    protected static string $resource = PtjResource::class;

    protected static ?string $title = 'Tambah PTJ';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

}
