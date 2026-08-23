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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LetakJawatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('nama')
                    ->label('Pegawai')
                    ->formatStateUsing(
                        fn ($record) => '<strong>'.($record->nama ?? '-').'</strong><br>'.
                        (
                            $record->jawatan_gred
                            ? $record->jawatan_gred->jawatan->desc_jawatan.
                            ' ('.$record->jawatan_gred->gred->kod_gred.')'
                            : '-'
                        )
                    )
                    ->html()
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('nokp', 'like', "%{$search}%")
                            ->orWhereHas('jawatan_gred.jawatan', function ($q) use ($search) {
                                $q->where('desc_jawatan', 'like', "%{$search}%");
                            })
                            ->orWhereHas('jawatan_gred.gred', function ($q) use ($search) {
                                $q->where('kod_gred', 'like', "%{$search}%");
                            });
                    }),

                TextColumn::make('jenis_notis')
                    ->label('Notis')
                    ->formatStateUsing(
                        fn ($record) => '<strong>'.($record->jenis_notis ?? '-').'</strong><br>'.
                        'Tarikh Kuatkuasa: '.Carbon::parse($record->tarikh_kuatkuasa)->format('d F Y')
                    )
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $query->where('jenis_notis', 'like', "%{$search}%")
                            ->orWhere('tarikh_kuatkuasa', 'like', "%{$search}%")
                            ->orWhere('tarikh_notis', 'like', "%{$search}%");
                    })
                    ->sortable(),

                TextColumn::make('lantikan')
                    ->label('Lantikan')
                    ->formatStateUsing(
                        fn ($record) => '<strong>'.($record->ptj->nama_ptj).'</strong><br>'.
                        'Jenis Lantikan: '.($record->lantikan)
                    )
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $query->where('lantikan', 'like', "%{$search}%")
                            ->orWhereHas('ptj', function ($q) use ($search) {
                                $q->where('nama_ptj', 'like', "%{$search}%");
                            });
                    }),
            ])
            ->filters([
                SelectFilter::make('ptj')
                    ->label('PTJ')
                    ->relationship('ptj', 'nama_ptj')
                    ->searchable()
                    ->preload(),

            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modal()
                        ->modalHeading(fn ($record) => $record->nama)
                        ->extraModalFooterActions([
                            Action::make('edit')
                                ->label('Edit')
                                ->url(fn ($record) => LetakJawatanResource::getUrl('edit', [
                                    'record' => $record,
                                ])),
                        ]),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn ($record) => "Padam {$record->nama}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal'),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
