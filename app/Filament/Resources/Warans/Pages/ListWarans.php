<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListWarans extends ListRecords
{
    protected static string $resource = WaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Waran'),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'active' => Tab::make('Active'),
            'inactive' => Tab::make('Inactive'),
        ];
    }
}
