<?php

namespace App\Filament\Resources\WaranJawatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WaranJawatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowindex(),
                TextColumn::make('pegawai_id')
                    ->label('Pegawai')
                    ->getStateUsing(function ($record) {
                        if (!$record->pegawai) {
                            return '<span class="italic text-gray-500">Tiada penyandang</span>';
                        }

                        return '<strong>' . e($record->pegawai->nama) . '</strong><br>
                        <span class="text-sm text-gray-600">' . e($record->pegawai->nokp) . '</span>';
                    })

                    ->html()
                    ->wrap()
                    ->sortable()
                    // ->searchable(),
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('pegawai', function ($q) use ($search) {
                            $q->where('nama', 'like', "%{$search}%")
                                ->orwhere('nokp', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('waran.no_waran')
                    ->label('No Waran')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('butiran')
                    ->label('Butiran')
                    ->html()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('aktiviti')
                    ->label('Aktiviti')
                    ->formatStateUsing(
                        fn($record) =>
                        ($record->aktiviti?->no_aktivit) . ' - ' . ($record->aktiviti?->nama_aktiviti)
                    )
                    ->html()
                    ->wrap()
                    ->sortable(
                        query: function ($query, string $direction) {
                            $query
                                ->leftJoin('aktivitis', 'waran_jawatans.aktiviti_id', '=', 'aktivitis.id')
                                ->orderBy('aktivitis.no_aktivit', $direction)
                                ->select('waran_jawatans.*');
                        }
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('aktiviti', function ($q) use ($search) {
                            $q->where('no_aktivit', 'like', "%{$search}%")
                                ->orWhere('nama_aktiviti', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->size('lg')
                    ->sortable()
                    // ->searchable()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'removed' => 'Dibuang',
                        'pindaan nama' => 'Pindaan Nama',
                        'batal nama' => 'Batal Nama',
                        default => 'Aktif',
                    })
                    ->color(
                        fn($state) => match ($state) {
                            'removed' => 'danger',
                            'pindaan nama' => 'neutral',
                            'batal nama' => 'quartenary',
                            default => 'success',
                        }
                    )
                    ->searchable(
                        query: function ($query, string $search) {
                            $map = [
                                'aktif' => 'active',
                                'dibuang' => 'removed',
                                'pindaan nama' => 'pindaan nama',
                                'batal nama' => 'batal nama',
                            ];

                            $search = strtolower($search);

                            foreach ($map as $label => $value) {
                                if (str_contains($label, $search)) {
                                    $query->where('status', $value);
                                    return;
                                }
                            }
                        }
                    )
                // TextColumn::make('status')
                // ->label('Status')


            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modal(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
