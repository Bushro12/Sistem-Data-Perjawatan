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
                                    collect(range(now()->year - 5, now()->year + 5))
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
}
