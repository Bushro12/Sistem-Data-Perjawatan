<?php

namespace App\Filament\Resources\Jawatans\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Pest\Support\View;

class JawatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),
                TextColumn::make('desc_jawatan')
                    ->label('Jawatan')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('kod_jawatan')
                    ->label('Kod Jawatan')
                    ->sortable()
                    ->searchable(),
                // ->badge(),
                // TextColumn::make('greds.kod_gred')
                //     ->label('Gred')
                //     ->formatStateUsing(fn($state) => collect($state)->unique()->implode(' / '))
                //     ->searchable()
                //     ->wrap()
                //     ->badge()
                //    ->colors([
                //         1 => 'danger',
                //         2 => 'info',
                //         3 => 'success',
                //         4 => 'primary',
                //         5 => 'gray'
                //     ]),
                TextColumn::make('greds.kod_gred')
                    ->label('Gred')
                    ->formatStateUsing(fn($state) => collect($state)->unique()->implode(' / '))
                    ->badge()
                    ->wrap()
                    ->color(function ($record) {

                        $kumpulanId = $record->greds->first()?->pivot?->kumpulan_id;

                        return match ($kumpulanId) {
                            1 => 'danger',
                            2 => 'info',
                            3 => 'tertiary',
                            4 => 'primary',
                            5 => 'secondary',
                            default => 'gray',
                        };
                    })

            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ActionGroup::make([
                //     ViewAction::make()
                //         ->color('blue')
                //         ->icon(Heroicon::PencilSquare)
                //         ->hiddenLabel(),
                //     Action::make('delete')
                //         ->color('gray')
                //         ->icon(Heroicon::Trash)
                //         ->hiddenLabel(),
                // ])
                // ->buttonGroup(),
                ViewAction::make()
                    ->modal()
                    ->label('')
                    ->tooltip('View')
                    ->color('info'),
                EditAction::make()
                    ->modal()
                    ->label('')
                    ->tooltip('Edit'),
                DeleteAction::make()
                    ->modal()
                    ->label('')
                    ->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
