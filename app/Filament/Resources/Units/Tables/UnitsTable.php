<?php

namespace App\Filament\Resources\Units\Tables;

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
                    ->rowIndex(),
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
                ViewAction::make()
                    ->label('')
                    ->color('info'),
                EditAction::make()
                    ->label('')
                    ->modal(),
                DeleteAction::make()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
