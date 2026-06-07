<?php

namespace App\Filament\Resources\Warans\Tables;

use App\Models\Program;
use App\Models\WaranJawatan;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WaransTable
{
    public static function configure(Table $table): Table
    {
        return $table

            // ✅ ONE correct eager load only
            ->modifyQueryUsing(
                fn($query) =>
                $query->with([
                    'waranJawatan.ptj',
                    'waranJawatan.aktiviti',
                ])
            )

            // ->modifyQueryUsing(function ($query) {

            //     $query
            //         ->leftJoin('waran_jawatans', 'waran_jawatans.waran_id', '=', 'warans.id')
            //         ->leftJoin('aktivitis', 'aktivitis.id', '=', 'waran_jawatans.aktiviti_id')
            //         ->leftJoin('programs', 'programs.id', '=', 'aktivitis.program_id')

            //         ->select([
            //             'warans.*',
            //             'programs.id as grouped_program_id',
            //             'programs.nama_program',
            //         ])

            //         ->groupBy(
            //             'warans.id',
            //             'programs.id',
            //             'programs.nama_program'
            //         );
            // })

//             ->modifyQueryUsing(function ($query) {

//     $query->with([
//         'waranJawatan.aktiviti.program',
//     ]);
// })

            ->columns([

                // Bil
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),

                // Waran Info
                TextColumn::make('no_waran')
                    ->label('Maklumat Waran')
                    // ->formatStateUsing(function ($record) {

                    //     // $symbol = match ($record->jenis) {
                    //     //     'tambah' => '+',
                    //     //     'tolak' => '-',
                    //     //     default => '',
                    //     // };

                    //     // return '<strong>' . $record->no_waran . '</strong><br>'
                    //     //     . 'Jawatan: ' . $symbol . '' . $record->jik;
                    // })
                    // ->html()
                    ->searchable(),

                TextColumn::make('butiran_list')
                    ->label('Butiran')
                    ->html()
                   ->searchable(query: function ($query, $search) {
                        $query->whereHas('waranJawatan', function ($q) use ($search) {
                            $q->where('butiran', 'like', "%{$search}%");
                        });

                    }),
                // Aktiviti (unique, no repeat)
                TextColumn::make('aktiviti_list')
                    ->label('Aktiviti')
                    ->formatStateUsing(function ($record) {

                        $items = $record->jenis === 'tolak'
                            ? WaranJawatan::withTrashed()
                                ->where('waran_tolak_id', $record->id)
                                ->with('aktiviti')
                                ->get()
                            : $record->waranJawatan;

                        return $items
                            ->map(
                                fn($wj) =>
                                $wj->aktiviti
                                ? $wj->aktiviti->no_aktivit . ' - ' . $wj->aktiviti->nama_aktiviti
                                : null
                            )
                            ->filter()
                            ->unique()
                            ->join('<br>');
                    })
                    ->html()
                    ->wrap()
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('waranJawatan.aktiviti', function ($q) use ($search) {
                            $q->where('nama_aktiviti', 'like', "%{$search}%")
                                ->orWhere('no_aktivit', 'like', "%{$search}%");
                        })
                            ->orWhereHas('waranJawatan', function ($q) use ($search) {
                                $q->whereHas('aktiviti', function ($q2) use ($search) {
                                    $q2->where('nama_aktiviti', 'like', "%{$search}%")
                                        ->orWhere('no_aktivit', 'like', "%{$search}%");
                                });
                            });
                    }),

                // TextColumn::make('aktiviti_list')
                //     ->label('Aktiviti')
                //     ->html()
                //     ->formatStateUsing(function ($record) {

                //         return WaranJawatan::query()
                //             ->where('waran_id', $record->id)
                //             ->whereHas('aktiviti', function ($q) use ($record) {
                //                 $q->where('program_id', $record->grouped_program_id);
                //             })
                //             ->with('aktiviti')
                //             ->get()

                //             ->map(
                //                 fn($wj) =>
                //                 $wj->aktiviti
                //                 ? $wj->aktiviti->no_aktivit . ' - ' . $wj->aktiviti->nama_aktiviti
                //                 : null
                //             )
                //             ->filter()
                //             ->unique()
                //             ->join('<br>');


                //     }),



                TextColumn::make('penempatan_list')
                    ->label('Penempatan')
                    ->html()
                    ->wrap()
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('waranJawatan.ptj', function ($q) use ($search) {
                            $q->where('nama_ptj', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('jik')
                    ->label('J')
                    ->formatStateUsing(function ($state, $record) {

                        return $record->jenis === 'tolak'
                            ? '-' . $state
                            : '+' . $state;
                    }),

                TextColumn::make('isi_count')
                    ->label('I'),

                TextColumn::make('kosong_count')
                    ->label('K'),


                TextColumn::make('status_jik')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {

                        return match (true) {
                            $record->status_jik === 'Seimbang' => 'success',

                            $record->jenis === 'tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis === 'tolak' && $record->status_jik === 'Kurang' => 'warning',

                            $record->jenis !== 'tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis !== 'tolak' && $record->status_jik === 'Kurang' => 'warning',

                            default => 'gray',
                        };
                    })
                    ->searchable(query: function ($query, $search) {

                        $query->where(function ($q) use ($search) {

                            $countSub = '(SELECT COUNT(*) FROM waran_jawatans WHERE waran_jawatans.waran_id = warans.id)';

                            if (str_contains(strtolower($search), 'seimbang')) {
                                $q->orWhereRaw("jik = $countSub");
                            }

                            if (str_contains(strtolower($search), 'kurang')) {
                                $q->orWhereRaw("jik > $countSub");
                            }

                            if (str_contains(strtolower($search), 'lebih')) {
                                $q->orWhereRaw("jik < $countSub");
                            }

                        });

                    })
            ])

            ->filters([
                SelectFilter::make('program')
                    ->label('Program')
                    ->relationship('waranJawatan.aktiviti.program', 'nama_program')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('aktiviti')
                    ->label('Aktiviti')
                    ->relationship('waranJawatan.aktiviti', 'nama_aktiviti')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) =>
                        $record->no_aktivit . ' - ' . $record->nama_aktiviti
                    )
                    ->searchable()
                    ->preload()

            ])
            ->recordActions([
                ActionGroup::make([
                EditAction::make(),
                DeleteAction::make()
                ])
                // DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
