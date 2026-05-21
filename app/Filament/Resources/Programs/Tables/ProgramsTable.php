<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama_program', 'asc')

            ->columns([
                // TextColumn::make('no')
                //     ->label('Bil')
                //     ->rowIndex(),
                // TextColumn::make('nama_program')
                //     ->label('Program')
                //     ->sortable()
                //     ->searchable(),
                // TextColumn::make('aktiviti')
                //     ->label('Aktiviti')
                //     ->getStateUsing(
                //         fn($record) =>
                //         $record->aktiviti
                //             ->map(fn($item) => $item->no_aktivit . ' - ' . $item->nama_aktiviti)
                //             ->toArray()
                //     )
                //     ->listWithLineBreaks()
                //     ->searchable(query: function ($query, $search) {
                //         $query->orWhereHas('aktiviti', function ($q) use ($search) {
                //             $q->where('nama_aktiviti', 'like', "%{$search}%")
                //                 ->orWhere('no_aktivit', 'like', "%{$search}%");
                //         });
                //     }),
                // TextColumn::make('butiran')
                //     ->label('Butiran')
                //     ->getStateUsing(function ($record) {
                //         return $record->aktiviti
                //             ->flatMap(function ($aktiviti) {
                //                 return $aktiviti->butiran
                //                     ->map(fn($b) => $b->butiran);
                //             })
                //             ->toArray();
                //     })
                //     ->listWithLineBreaks()

                TextColumn::make('no')
                    ->label("Bil")
                    ->rowindex(),
                TextColumn::make('desc_program')
                    ->getStateUsing(
                        fn($record) =>
                        $record->aktiviti
                            ->map(fn($a) => $a->program?->nama_program . ' - ' . $a->program?->desc_program)
                            ->filter()
                            ->unique()
                            ->join(', ')
                    )
                    ->badge()
                    ->wrap()
                    // ->sortable()
                    ->searchable(query: function ($query, $search) {
                        $query->where('nama_program', 'like', "%{$search}%")
                            ->orWhere('desc_program', 'like', "%{$search}%")
                            ->orWhereHas('aktiviti', function ($q) use ($search) {
                                $q->where('no_aktivit', 'like', "%{$search}%")
                                    ->orWhere('nama_aktiviti', 'like', "%{$search}%");
                            });
                    })
                        ,
                // ->defaultSort('nama_program'),


                TextColumn::make('aktiviti')
                    ->label('Aktiviti')
                    ->getStateUsing(
                        fn($record) =>
                        $record->aktiviti
                            ->map(fn($item) => $item->no_aktivit . ' - ' . $item->nama_aktiviti)
                            ->toArray()
                    )
                    ->wrap()
                    ->listWithLineBreaks(),

                // TextColumn::make('butiran')
                //     ->label('Butiran')
                //     ->getStateUsing(function ($record) {
                //         return $record->aktiviti
                //             ->flatMap(function ($aktiviti) {
                //                 return $aktiviti->butiran
                //                     ->pluck('butiran');
                //             })
                //             ->toArray();
                //     })
                //     ->listWithLineBreaks(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make()
                //     ->label('View')
                //     ->color('info')
                //     ->tooltip('View'),
                EditAction::make()
                    ->label('Edit')
                    ->tooltip('Edit'),
                DeleteAction::make()
                    ->label('Delete')
                    ->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
