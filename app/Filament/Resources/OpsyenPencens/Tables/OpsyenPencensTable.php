<?php

namespace App\Filament\Resources\OpsyenPencens\Tables;

use Filament\Actions\ActionGroup;
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
               ActionGroup::make([
                    // ViewAction::make(),
                    EditAction::make()
                        // ->label('Kemaskini')
                        ->modal()
                        // ->modalHeading('Kemaskini Rekod')
                        ->modalSubmitActionLabel('Simpan')
                        ->modalCancelActionLabel('Batal'),

                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->opsyen}")
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
