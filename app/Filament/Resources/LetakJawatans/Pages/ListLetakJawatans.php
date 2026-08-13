<?php

namespace App\Filament\Resources\LetakJawatans\Pages;
use Filament\Forms\Components\Columns;
use App\Filament\Exports\LetakJawatanExporter;
use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use App\Models\LetakJawatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;



class ListLetakJawatans extends ListRecords
{
    protected static string $resource = LetakJawatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Letak Jawatan'),
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('export')
                ->modalHeading('Export Laporan Letak Jawatan')
                ->modalSubmitActionLabel('Export')
                ->modalCancelActionLabel('Batal')
                ->form([
                    Grid::make(2)
                        ->schema([
                            Select::make('from_month')
                                ->label('Dari Bulan')
                                ->options([
                                    '1' => 'Januari',
                                    '2' => 'Februari',
                                    '3' => 'Mac',
                                    '4' => 'April',
                                    '5' => 'Mei',
                                    '6' => 'Jun',
                                    '7' => 'Julai',
                                    '8' => 'Ogos',
                                    '9' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Disember',
                                ])
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('from_year')
                                ->label('Dari Tahun')
                                ->options(
                                    collect(range(now()->year - 2, now()->year + 2))
                                        ->mapWithKeys(fn($year) => [$year => $year])
                                        ->toArray()
                                )
                                ->required()
                                ->searchable()
                                ->preload(),

                        ]),

                    Grid::make(2)
                        ->schema([
                            Select::make('to_month')
                                ->label('Hingga Bulan')
                                ->options([
                                    '1' => 'Januari',
                                    '2' => 'Februari',
                                    '3' => 'Mac',
                                    '4' => 'April',
                                    '5' => 'Mei',
                                    '6' => 'Jun',
                                    '7' => 'Julai',
                                    '8' => 'Ogos',
                                    '9' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Disember',
                                ])
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('to_year')
                                ->label('Hingga Tahun')
                                ->options(
                                    collect(range(now()->year - 2, now()->year + 2))
                                        ->mapWithKeys(fn($year) => [$year => $year])
                                        ->toArray()
                                )
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])

                ])
                ->action(function (array $data) {
                    return redirect()->route('export.letakJawatan', [
                        'from_month' => $data['from_month'],
                        'from_year' => $data['from_year'],
                        'to_month' => $data['to_month'],
                        'to_year' => $data['to_year'],
                    ]);
                }),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
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
