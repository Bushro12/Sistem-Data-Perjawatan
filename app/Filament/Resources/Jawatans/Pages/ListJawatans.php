<?php

namespace App\Filament\Resources\Jawatans\Pages;

use App\Filament\Resources\Jawatans\JawatanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJawatans extends ListRecords
{
    protected static string $resource = JawatanResource::class;

    protected function getHeaderActions(): array
    {


        return [
            CreateAction::make()
                ->label('Tambah Jawatan')
                ->modal()
                ->createAnother(false),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\JawatanLegend::class,
        ];
    }
}
