<?php

namespace App\Filament\Resources\Greds\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GredsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('kod_gred')
                    ->label('Kod')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('desc_gred')
                    ->label('Gred')
                    ->sortable()
                    ->searchable(),
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
                        ->modalHeading(fn($record) => "Padam {$record->kod_gred}")
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
