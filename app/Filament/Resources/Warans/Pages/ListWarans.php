<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use App\Filament\Resources\Warans\Widgets\WaranStats;
use App\Models\Program;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

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
    $tabs = [
        'all' => Tab::make('All'),
    ];

    foreach (Program::orderBy('nama_program')->get() as $program) {

        $tabs[$program->id] = Tab::make($program->nama_program)
    ->modifyQueryUsing(function (Builder $query) use ($program) {

        $query
            ->whereHas('waranJawatan.aktiviti', function ($q) use ($program) {
                $q->where('program_id', $program->id);
            })
            ->groupBy('warans.id');
    });
    }

    return $tabs;
}

    protected function getHeaderWidgets(): array
{
    return [
        WaranStats::class,
    ];
}
}
