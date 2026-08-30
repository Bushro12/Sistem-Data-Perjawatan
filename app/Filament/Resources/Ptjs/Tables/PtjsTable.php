<?php

namespace App\Filament\Resources\Ptjs\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PtjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('nama_ptj')
                    ->label('Nama PTJ')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                // TextColumn::make('kod_ptj')
                //     ->label('Kod PTJ')
                //     ->searchable()
                //     ->sortable(),
                // TextColumn::make('pengarah')
                //     ->label('Pengarah')
                //     ->searchable()
                //     ->sortable()
                //     ->wrap(),
                TextColumn::make('parlimen.nama_parlimen')
                    ->label('Parlimen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dun.nama_dun')
                    ->label('Dun')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('parlimen_id')
                    ->label('Parlimen')
                    ->relationship('parlimen', 'nama_parlimen')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('dun_id')
                    ->label('Dun')
                    ->relationship('dun', 'nama_dun')
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    // ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                ]),
            ]);
    }
}
