<?php

namespace App\Filament\Resources\Pegawais\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\View\Components\BadgeComponent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->nama ?? '-') . '</strong><br>' .
                        ($record->nokp ?? '-') . '<br>' .
                            // ($record->emel ?? '-') . '<br>' .
                        (
                            $record->jawatan_gred
                            ? $record->jawatan_gred->jawatan->desc_jawatan .
                            ' (' . $record->jawatan_gred->gred->kod_gred . ')'
                            : '-'
                        )
                    )
                    ->html(),
                // TextColumn::make('nama')
                //     ->label('Pegawai')
                //     ->formatStateUsing(function ($record) {

                //         $status = match (true) {
                //             $record->is_tetap == 1 => 'Tetap',
                //             $record->is_kontrak == 1 => 'Kontrak',
                //             $record->is_kontrak_interim == 1 => 'Kontrak Interim',
                //             default => '-',
                //         };

                //         return
                //             '<strong>' . ($record->nama ?? '-') . '</strong><br>' .
                //             ($record->nokp ?? '-') . '<br>' .
                //             (
                //                 $record->jawatan_gred
                //                 ? $record->jawatan_gred->jawatan->desc_jawatan .
                //                 ' (' . $record->jawatan_gred->gred->kod_gred . ')'
                //                 : '-'
                //             ) . '<br>' .
                //             '<span class="text-gray-500">' . $status . '</span>';
                //     })
                //     ->html(),
                TextColumn::make('ptj')
                    ->label('Penempatan')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . $record->ptj->nama_ptj . '</strong><br>' . $record->bahagian->nama_bahagian .
                        '<br>' . $record->unit->nama_unit . '<br>' . $record->subunit->nama_subunit

                    )
                    ->html(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {

                        return match (true) {
                            $record->is_tetap == 1 => 'Tetap',
                            $record->is_kontrak == 1 => 'Kontrak',
                            $record->is_kontrak_interim == 1 => 'Kontrak Interim',
                            default => '-',
                        };
                    })
                    ->color(function ($state) {

                        return match ($state) {
                            'Tetap' => 'success',
                            'Kontrak' => 'warning',
                            'Kontrak Interim' => 'info',
                            default => 'gray',
                        };
                    }),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
