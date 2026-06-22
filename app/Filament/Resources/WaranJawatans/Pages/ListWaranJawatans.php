<?php

namespace App\Filament\Resources\WaranJawatans\Pages;

use App\Filament\Resources\WaranJawatans\WaranJawatanResource;
use App\Models\Program;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWaranJawatans extends ListRecords
{
    protected static string $resource = WaranJawatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Penyandang'),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        foreach (Program::orderBy('nama_program')->get() as $program) {
            $tabs[$program->id] = Tab::make($program->nama_program)
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->whereRelation(
                        'aktiviti',
                        'program_id',
                        $program->id
                    )
                );
        }

        return $tabs;
    }
}
