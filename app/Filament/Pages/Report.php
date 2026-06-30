<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\Concerns\InteractsWithInput;

class Report extends Page implements HasTable
{

    use InteractsWithTable;
    protected string $view = 'filament.pages.report';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;


    protected static ?string $navigationLabel = 'Senarai Laporan';

    protected static ?string $title = 'Senarai Laporan';


    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 15;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                ->label('Bil'),

                TextColumn::make('name')
                    ->label('Nama Laporan')
                    ->sortable()
                    ->searchable(),

                // TextColumn::make('description')
                //     ->label('Penerangan')
                //     ->sortable()
                //     ->searchable(),
            ])
            ->records(function () {

                $search = $this->getTableSearch();

                $data = [
                    [
                        'id' => 1,
                        'name' => 'Data Keseluruhan Mengikut PTJ',
                        // 'description' => 'Senarai semua pegawai dalam sistem',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Data Perjawatan Kontrak',
                        // 'description' => 'Laporan waran jawatan terkini',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Data Mengikut Kupulan Mengikut PTJ',
                        // 'description' => 'Senarai jawatan kosong',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Data Keseluruhan Mengikut Jawatan',
                        // 'description' => 'Senarai jawatan kosong',
                    ],
                    [
                        'id' => 5,
                        'name' => 'Laporan JIK Mengikut Jawatan',
                        // 'description' => 'Senarai jawatan kosong',
                    ],
                    [
                        'id' => 6,
                        'name' => 'Laporan JIK Mengikut Gred',
                        // 'description' => 'Senarai jawatan kosong',
                    ],
                ];

                if (!$search) {
                    return $data;
                }

                return collect($data)
                    ->filter(function ($item) use ($search) {
                        return str_contains(strtolower($item['name']), strtolower($search));
                            // || str_contains(strtolower($item['description']), strtolower($search));
                    })
                    ->values()
                    ->toArray();
            })
            ->actions([
                Action::make('download')
                    ->label('Muat Turun')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {

                        return match ($record['id']) {
                            1 => redirect()->route('export.dataKeseluruhan'),
                            2 => redirect()->route('report.waran.export'),
                            3 => redirect()->route('report.kosong.export'),
                        };
                    }),
            ]);
    }
}
