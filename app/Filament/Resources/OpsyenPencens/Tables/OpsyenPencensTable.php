<?php

namespace App\Filament\Resources\OpsyenPencens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OpsyenPencensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                ->label('Bil')
                ->rowIndex(),
                TextColumn::make('opsyen')
                ->label('Opsyen Pencen')
                ->sortable()
                ->searchable()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                ->modal(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
