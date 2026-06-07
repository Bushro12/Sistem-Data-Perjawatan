<?php

namespace App\Filament\Resources\Subunits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubunitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                ->label('Bil')
                ->rowIndex(),
                TextColumn::make('unit.nama_unit')
                ->label('Unit'),
                TextColumn::make('nama_subunit')
                ->label('Sub Unit'),
                TextColumn::make('parlimen.nama_parlimen')
                ->label('Parlimen'),
                TextColumn::make('dun.nama_dun')
                ->label('Dun')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->color("info")
                    ->label("")
                    ->tooltip("View"),
                EditAction::make()
                    ->modal()
                    ->label("")
                    ->tooltip("Edit"),
                DeleteAction::make()
                    ->label("")
                    ->tooltip("Delete"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
