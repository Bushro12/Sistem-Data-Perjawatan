<?php

namespace App\Filament\Resources\Kumpulans\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KumpulansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->Label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('nama_kumpulan')
                    ->label('Kumpulan')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        // ->label('Kemaskini')
                        ->modal()
                        // ->modalHeading('Kemaskini Rekod')
                        ->modalSubmitActionLabel('Simpan')
                        ->modalCancelActionLabel('Batal'),

                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->nama_kumpulan}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')

                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
