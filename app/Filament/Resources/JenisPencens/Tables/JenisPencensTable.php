<?php

namespace App\Filament\Resources\JenisPencens\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JenisPencensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    // ->toggleable(false)
                    // ->alignCenter()
                    ->width(1),

                TextColumn::make('jenis')
                    ->label('Jenis Penamatan Perkhidmatan')
                    ->sortable()
                    ->searchable()
                    // ->icon('heroicon-o-document-text')
                    // ->iconColor('primary')
                    ->wrap(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pilihan' => 'success',
                        'Paksa' => 'warning',
                        default => 'gray',
                    })
                    // ->icon(fn(string $state): string => match ($state) {
                    //     'Pilihan' => 'heroicon-o-check-badge',
                    //     'Paksa' => 'heroicon-o-x-circle',
                    //     default => 'heroicon-o-question-mark-circle',
                    // })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->tooltip('Kemaskini')
                        ->modal()
                        ->modalSubmitActionLabel('Simpan')
                        ->modalCancelActionLabel('Batal')
                        ->color('warning'),

                    DeleteAction::make()
                        ->label('Padam')
                        ->icon('heroicon-o-trash')
                        ->tooltip('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->jenis}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
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

            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('Tiada Jenis Penamatan')
            ->emptyStateDescription('Belum ada rekod jenis penamatan perkhidmatan. Klik butang "Cipta" untuk tambah rekod baru.');
    }
}
