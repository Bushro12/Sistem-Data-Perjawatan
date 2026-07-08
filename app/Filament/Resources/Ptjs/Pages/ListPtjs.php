<?php

namespace App\Filament\Resources\Ptjs\Pages;

use App\Filament\Resources\Ptjs\PtjResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPtjs extends ListRecords
{
    protected static string $resource = PtjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah PTJ')
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
