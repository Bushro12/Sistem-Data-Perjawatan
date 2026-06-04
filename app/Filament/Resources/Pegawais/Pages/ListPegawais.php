<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Filament\Resources\Pegawais\PegawaiResource;
use App\Models\Pegawai;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPegawais extends ListRecords
{
    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ALL'),
            // ->badge(Pegawai::count()),

            'tetap' => Tab::make('TETAP')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_tetap', 1)
                ),
            // ->badge(Pegawai::where('is_tetap', 1)->count()),

            'kontrak_interim' => Tab::make('KONTRAK INTERIM')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak_interim', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak_interim', 1)->count()),

            'kontrak' => Tab::make('KONTRAK')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak', 1)->count()),


        ];
    }
}
