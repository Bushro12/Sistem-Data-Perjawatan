<?php

namespace App\Filament\Pages;

use App\Models\Jawatan;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\Concerns\InteractsWithInput;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;

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
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->columns([
                TextColumn::make('id')
                    ->label('Bil')
                    ->extraHeaderAttributes(['style' => 'width: 60px'])
                    ->extraCellAttributes(['style' => 'width: 60px']),

                TextColumn::make('name')
                    ->label('Nama Laporan')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
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
                        'name' => 'Data Mengikut Kumpulan Mengikut PTJ',
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

                    ->form(function ($record) {

                        return match ($record['id']) {

                            5 => [
                                Select::make('jawatan_id')
                                    ->label('Pilih Jawatan')
                                    ->options(
                                        Jawatan::orderBy('desc_jawatan')
                                            ->pluck('desc_jawatan', 'id')
                                    )
                                    ->searchable()
                                    ->required(),
                            ],
                          

                            default => [],
                        };

                    })

                    ->action(function ($record, array $data) {

                        return match ($record['id']) {

                            1 => redirect()->route('export.dataKeseluruhan'),

                            2 => redirect()->route('export.dataKontrak'),

                            3 => redirect()->route('report.kosong.export'),

                            5 => redirect()->route('export.jikByJawatan', [
                                'jawatan_id' => $data['jawatan_id'],
                            ]),

                        };

                    }),

            ]);
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
