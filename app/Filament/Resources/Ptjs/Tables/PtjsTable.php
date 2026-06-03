<?php

namespace App\Filament\Resources\Ptjs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PtjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('nama_ptj')
                    ->label('Nama PTJ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kod_ptj')
                    ->label('Kod PTJ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pengarah')
                    ->label('Pengarah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pengarah')
                    ->label('Pengarah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parlimen.nama_parlimen')
                    ->label('Parlimen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dun.nama_dun')
                    ->label('Dun')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('nama_ptj', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                ->label("")
                ->color("info"),
                EditAction::make()
                ->label("")
                ->modal(),
                DeleteAction::make()
                ->label(""),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
