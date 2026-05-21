<?php

namespace App\Filament\Resources\Warans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaransTable
{
    public static function configure(Table $table): Table
    {
        return $table

            // ✅ ONE correct eager load only
            ->modifyQueryUsing(
                fn($query) =>
                $query->with([
                    'waranJawatan.ptj',
                    'waranJawatan.aktiviti',
                ])
            )

            ->columns([

                // Bil
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),

                // Waran Info
                TextColumn::make('no_waran')
                    ->label('Maklumat Waran')
                    ->formatStateUsing(function ($record) {

                        $symbol = match ($record->jenis) {
                            'tambah' => '+',
                            'tolak' => '-',
                            default => '',
                        };

                        return '<strong>' . $record->no_waran . '</strong><br>'
                            . 'Jawatan: ' . $symbol . '' . $record->jik;
                    })
                    ->html(),
                TextColumn::make('butiran_list')
                    ->label('Butiran')
                    ->html(),
                // Aktiviti (unique, no repeat)
                TextColumn::make('aktiviti_list')
                    ->label('Aktiviti')
                    ->formatStateUsing(function ($record) {

                        $items = $record->jenis === 'tolak'
                            ? \App\Models\WaranJawatan::withTrashed()
                                ->where('waran_tolak_id', $record->id)
                                ->with('aktiviti')
                                ->get()
                            : $record->waranJawatan;

                        return $items
                            ->map(
                                fn($wj) =>
                                $wj->aktiviti
                                ? $wj->aktiviti->no_aktivit . ' - ' . $wj->aktiviti->nama_aktiviti
                                : null
                            )
                            ->filter()
                            ->unique()
                            ->join('<br>');
                    })
                    ->html()
                    ->wrap(),

                TextColumn::make('penempatan_list')
                    ->label('Penempatan')
                    ->html()
                    ->wrap(),

                TextColumn::make('jik')
                    ->label('J')
                    ->formatStateUsing(function ($state, $record) {

                        return $record->jenis === 'tolak'
                            ? '-' . $state
                            : '+' . $state;
                    }),

                TextColumn::make('isi_count')
                    ->label('I'),

                TextColumn::make('kosong_count')
                    ->label('K'),


                TextColumn::make('status_jik')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {

                        return match (true) {
                            $record->status_jik === 'Seimbang' => 'success',

                            $record->jenis === 'tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis === 'tolak' && $record->status_jik === 'Kurang' => 'warning',

                            $record->jenis !== 'tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis !== 'tolak' && $record->status_jik === 'Kurang' => 'warning',

                            default => 'gray',
                        };
                    }),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
