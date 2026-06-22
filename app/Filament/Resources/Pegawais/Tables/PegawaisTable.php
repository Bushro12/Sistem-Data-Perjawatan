<?php

namespace App\Filament\Resources\Pegawais\Tables;

use App\Models\Pegawai;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;

use Filament\Support\View\Components\BadgeComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Pest\Arch\GroupArchExpectation;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),
                TextColumn::make('nama')
                    ->label('Pegawai')
                    ->formatStateUsing(function ($record) {

                        $lantikan = match (true) {
                            $record->is_tetap == 1 => ['TETAP'],
                            $record->is_kontrak == 1 => ['KONTRAK'],
                            $record->is_kontrak_interim == 1 => ['KONTRAK INTERIM'],
                            default => ['-', 'gray'],
                        };

                        return
                            '<strong>' . ($record->nama ?? '-') . '</strong><br>' .
                                // ($record->nokp ?? '-') . '<br>' .
                            ($record->jawatan_gred
                                ? $record->jawatan_gred->jawatan->desc_jawatan .
                                ' (' . $record->jawatan_gred->gred->kod_gred . ')'
                                : '-') .
                            '<br>';
                        // . ($lantikan[0]);
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('nokp', 'like', "%{$search}%")
                            ->orWhereHas('jawatan_gred.jawatan', function ($q) use ($search) {
                                $q->where('desc_jawatan', 'like', "%{$search}%");
                            })
                            ->orWhereHas('jawatan_gred.gred', function ($q) use ($search) {
                                $q->where('kod_gred', 'like', "%{$search}%");
                            });
                    })
                    ->sortable(),

                TextColumn::make('ptj')
                    ->label('Penempatan')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->ptj?->nama_ptj ?? '') . '</strong><br>' .
                        ($record->bahagian?->nama_bahagian ?? '') . '<br>'
                        // .
                        // ($record->unit?->nama_unit ?? '') . '<br>' .
                        // ($record->subunit?->nama_subunit ?? '')
                    )

                    ->html()
                    ->sortable(
                        query: function ($query, string $direction) {
                            $query
                                ->leftJoin('ptjs', 'pegawais.ptj_id', '=', 'ptjs.id')
                                ->orderBy('ptjs.nama_ptj', $direction)
                                ->select('pegawais.*');
                        }
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('ptj', function ($q) use ($search) {
                            $q->where('nama_ptj', 'like', "%{$search}%");

                        })
                            ->orWhereHas('bahagian', function ($q) use ($search) {
                                $q->where('nama_bahagian', 'like', "%{$search}%");
                            });
                    }),

                TextColumn::make('waran')
                    ->label('Waran')
                    ->getStateUsing(function ($record) {

                        if ($record->is_jtw == 1) {
                            return '<strong>Jawatan tanpa waran</strong>';
                        }

                        return '<strong>' . ($record->waranJawatan?->first()?->waran?->no_waran ?? '') . '</strong>';
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('waranJawatan.waran', function ($q) use ($search) {
                            $q->where('no_waran', 'like', "%{$search}%");
                        });

                        if (str_contains(strtolower('jawatan tanpa waran'), strtolower($search))) {
                            $query->orWhere('is_jtw', 1);
                        }
                    })
                    ->sortable(
                        query: function ($query, string $direction) {
                            $query
                                ->leftJoin('waran_jawatans', 'pegawais.id', '=', 'waran_jawatans.pegawai_id')
                                ->leftJoin('warans', 'waran_jawatans.waran_id', '=', 'warans.id')
                                ->orderBy('warans.no_waran', $direction)
                                ->select('pegawais.*');
                        }
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (Pegawai $record) {

                        $noWaran = $record->waranJawatan?->first()?->waran?->no_waran;

                        $tidakLengkap =
                            is_null($record->ptj_id) ||
                            is_null($record->bahagian_id) ||
                            (
                                is_null($record->subunit_id) &&
                                $record->ada_unit == 0
                            ) ||
                            (
                                is_null($record->unit_id) &&
                                $record->ada_subunit == 0
                            ) ||
                            (
                                $record->is_jtw == 0 &&
                                is_null($noWaran)
                            );



                        return $tidakLengkap ? 'Tidak Lengkap' : 'Lengkap';
                    })
                    ->badge()
                    ->color(function (Pegawai $record) {

                        $noWaran = $record->waranJawatan?->first()?->waran?->no_waran;

                        $tidakLengkap =
                            is_null($record->ptj_id) ||
                            is_null($record->bahagian_id) ||
                            (
                                is_null($record->subunit_id) &&
                                $record->ada_unit == 0
                            ) ||
                            (
                                is_null($record->unit_id) &&
                                $record->ada_subunit == 0
                            ) ||
                            (
                                $record->is_jtw == 0 &&
                                is_null($noWaran)
                            );

                        return $tidakLengkap ? 'danger' : 'success';
                    })
                    ->searchable(
                        query: function ($query, string $search) {

                            $search = strtolower($search);

                            if (str_contains($search, 'tidak lengkap')) {
                                $query->where(function ($q) {
                                    $q->whereNull('ptj_id')
                                        ->orWhereNull('bahagian_id')

                                        ->orWhere(function ($q) {
                                            $q->whereNull('subunit_id')
                                                ->where('ada_unit', 0);
                                        })

                                        ->orWhere(function ($q) {
                                            $q->whereNull('unit_id')
                                                ->where('ada_subunit', 0);
                                        });
                                });
                            }
                            if (str_contains($search, 'lengkap') && !str_contains($search, 'tidak lengkap')) {
                                $query->whereNotNull('ptj_id')
                                    ->whereNotNull('bahagian_id')

                                    ->where(function ($q) {
                                        $q->whereNotNull('subunit_id')
                                            ->orWhere('ada_unit', 1);
                                    })

                                    ->where(function ($q) {
                                        $q->whereNotNull('unit_id')
                                            ->orWhere('ada_subunit', 1);
                                    });
                            }
                        }
                    )
                    ->sortable(
                        query: function ($query, string $direction) {

                            $query->orderByRaw("
                CASE
                    WHEN ptj_id IS NULL
                        OR bahagian_id IS NULL
                        OR subunit_id IS NULL
                        OR unit_id IS NULL
                    THEN 0
                    ELSE 1
                END {$direction}
            ");
                        }
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->nama}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                ])
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
