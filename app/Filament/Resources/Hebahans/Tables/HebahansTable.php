<?php

namespace App\Filament\Resources\Hebahans\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HebahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),

                TextColumn::make('tajuk')
                    ->label('Tajuk')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('tarikh_hebahan')
                    ->label('Tarikh Hebahan')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Diterbitkan',
                        'draft' => 'Draf',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('dipaparkan_sehingga')
                    ->label('Sehingga')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->defaultSort('tarikh_hebahan', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->tooltip('Kemaskini')
                        ->color('warning'),

                    DeleteAction::make()
                        ->label('Padam')
                        ->icon('heroicon-o-trash')
                        ->tooltip('Padam')
                        ->modalHeading(fn ($record) => "Padam {$record->tajuk}")
                        ->modalDescription('Adakah anda pasti mahu memadam hebahan ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->color('danger'),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('Tindakan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateHeading('Tiada Hebahan')
            ->emptyStateDescription('Belum ada hebahan. Klik butang "Cipta" untuk tambah hebahan baru.');
    }
}
