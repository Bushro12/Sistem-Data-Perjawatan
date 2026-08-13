<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('bahagian.nama_bahagian')
                    ->label('Bahagian')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('parlimen.nama_parlimen')
                    ->label('Parlimen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dun.nama_dun')
                    ->label('Dun')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->nama_unit}")
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
