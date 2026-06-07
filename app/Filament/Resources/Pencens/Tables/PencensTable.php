<?php

namespace App\Filament\Resources\Pencens\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PencensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),
                TextColumn::make('nama')
                    ->label('Nama Pegawai')
                    ->sortable()
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
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->nama ?? '-') . '</strong><br>' .
                            // ($record->nokp ?? '-') . '<br>' .
                        (
                            $record->jawatan_gred
                            ? $record->jawatan_gred->jawatan->desc_jawatan .
                            ' (' . $record->jawatan_gred->gred->kod_gred . ')'
                            : '-'
                        )
                    )
                    ->html(),
                TextColumn::make('ptj.nama_ptj')
                    ->label('Lantikan')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->ptj->nama_ptj ?? '-') . '</strong><br>' .
                        'Jenis Lantikan: ' . ($record->jenis_lantikan)
                    )
                    ->html()
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('ptj', function ($q) use ($search) {
                            $q->where('nama_ptj', 'like', "%{$search}%");
                        })
                        ->orWhere('jenis_lantikan', 'like', "%{$search}%");
                    }),

                TextColumn::make('jenisPencen.jenis')
                    ->label('Penamatan')
                    ->formatStateUsing(function ($record) {

                        $tarikh = $record->tarikh_kuatkuasa ?? $record->tarikh_pencen;

                        return
                            '<strong>' . ($record->jenisPencen->jenis ?? '-') . '</strong><br>' .
                            'Tarikh Kuatkuasa: ' . ($tarikh
                                ? Carbon::parse($tarikh)->translatedFormat('d F Y')
                                : '-');
                    })
                    ->html()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "COALESCE(tarikh_kuatkuasa, tarikh_pencen) {$direction}"
                        );
                    })
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->where(function ($query) use ($search) {
                                $query->where('tarikh_kuatkuasa', 'like', "%{$search}%")
                                    ->orWhere('tarikh_pencen', 'like', "%{$search}%")
                                    ->orWhereHas('jenisPencen', function ($q) use ($search) {
                                        $q->where('jenis', 'like', "%{$search}%");
                                    });
                            });
                        }
                    ),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    //  ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
