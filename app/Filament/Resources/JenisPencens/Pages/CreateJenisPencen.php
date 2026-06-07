<?php

namespace App\Filament\Resources\JenisPencens\Pages;

use App\Filament\Resources\JenisPencens\JenisPencenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJenisPencen extends CreateRecord
{
    protected static string $resource = JenisPencenResource::class;

    // protected static bool $canCreateAnother = false;

    // protected function getFormActions(): array
    // {
    //     return [
    //         $this->getCreateFormAction()
    //             ->label('Tambah'),

    //         $this->getCancelFormAction()
    //             ->label('Batal'),
    //     ];
    // }
}
