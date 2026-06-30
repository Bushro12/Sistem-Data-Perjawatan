<?php

namespace App\Filament\Resources\LetakJawatans\Tables;

use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LetakJawatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->recordUrl(null)
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),
                TextColumn::make('nama')
                    ->label('Pegawai')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->nama ?? '-') . '</strong><br>' .
                            // ($record->nokp ?? '-') . '<br>' .
                            // ($record->emel ?? '-') . '<br>' .
                        (
                            $record->jawatan_gred
                            ? $record->jawatan_gred->jawatan->desc_jawatan .
                            ' (' . $record->jawatan_gred->gred->kod_gred . ')'
                            : '-'
                        )
                    )
                    ->html()
                    ->sortable()
                    ->searchable(),


                TextColumn::make('jenis_notis')
                    ->label('Notis')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->jenis_notis ?? '-') . '</strong><br>' .
                        // 'Tarikh Mula Notis: ' . Carbon::parse($record->tarikh_notis)->format('d F Y') . '<br>' .
                        'Tarikh Kuatkuasa: ' . Carbon::parse($record->tarikh_kuatkuasa)->format('d F Y')

                    )
                    ->html()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lantikan')
                    ->label('Lantikan')
                    ->formatStateUsing(
                        fn($record) =>
                        '<strong>' . ($record->ptj->nama_ptj) . '</strong><br>' .
                        // 'Tarikh Lantikan: ' . Carbon::parse($record->tarikh_lantik)->format('d F Y') . '<br>' .
                        'Jenis Lantikan: ' . ($record->lantikan)
                    )
                    ->html()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                    ->modal()
                    ->modalHeading(fn($record) => $record->nama)
                     ->extraModalFooterActions([
                            Action::make('edit')
                                ->label('Edit')
                                ->url(fn($record) => LetakJawatanResource::getUrl('edit', [
                                    'record' => $record,
                                ])),
                        ]),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->nama}")
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
