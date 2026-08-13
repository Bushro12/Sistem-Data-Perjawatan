<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use App\Filament\Resources\Warans\Widgets\WaranStats;
use App\Models\Program;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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
    
            public function getBreadcrumbs(): array
    {
        // The back button (see getHeading()) replaces the need for a
        // breadcrumb trail on this page.
        return [];
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString(
            '<a href="' . e(\App\Filament\Pages\Dashboard::getUrl()) . '" class="mystaff-back-btn" aria-label="Kembali ke Dashboard">' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' .
            '</a>' .
            '<span>' . e($this->getTitle()) . '</span>'
        );
    }
}
