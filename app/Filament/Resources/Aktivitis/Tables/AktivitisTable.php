<?php

namespace App\Filament\Resources\Aktivitis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AktivitisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('no_aktivit')
                    ->label('No. Aktiviti')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_aktiviti')
                    ->label('Nama Aktiviti')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

            ])

            ->defaultSort('program_id', 'asc')

            ->recordActions([
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}