<?php

namespace App\Filament\Resources\WaranJawatans\Tables;

use App\Filament\Resources\WaranJawatans\Schemas\WaranJawatanInfolist;
use App\Filament\Resources\WaranJawatans\WaranJawatanResource;
use App\Filament\Support\BlockedPegawaiDelete;
use App\Models\WaranJawatan;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WaranJawatansTable
{
    public static function notifyBlockedPegawai(Collection $records): void
    {
        if ($records->contains(fn ($record): bool => $record->hasAssignedPegawai())) {
            BlockedPegawaiDelete::notify();
        }
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->recordUrl(null)
            ->defaultSort(fn (Builder $query): Builder => $query
                ->leftJoin('warans as waran_sort', 'waran_jawatans.waran_id', '=', 'waran_sort.id')
                ->orderBy('waran_sort.no_waran')
                ->orderBy('waran_jawatans.butiran')
                ->select('waran_jawatans.*'))
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowindex()
                    ->width(1),

                TextColumn::make('pegawai_id')
                    ->label('Pegawai')
                    ->getStateUsing(function ($record): string {
                        $statusLabel = match ($record->status) {
                            'removed' => 'Dibuang',
                            'pindaan nama' => 'Pindaan Nama',
                            'batal nama' => 'Batal Nama',
                            default => 'Aktif',
                        };

                        $statusStyle = match ($record->status) {
                            'removed' => 'background-color:#fee2e2;color:#b91c1c;',
                            'pindaan nama' => 'background-color:#dbeafe;color:#1d4ed8;',
                            'batal nama' => 'background-color:#fef3c7;color:#b45309;',
                            default => 'background-color:#dcfce7;color:#15803d;',
                        };

                        $statusBadge = '<span class="fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium" style="'.$statusStyle.'">'
                            .e($statusLabel)
                            .'</span>';

                        if (! $record->pegawai) {
                            return '<span class="italic text-gray-500">Tiada penyandang</span><br>'.$statusBadge;
                        }

                        return '<strong>'.e($record->pegawai->nama).'</strong><br>'
                            .'<span class="text-xs text-gray-500 dark:text-gray-400">'.e($record->pegawai->nokp).'</span><br>'
                            .$statusBadge;
                    })
                    ->html()
                    ->wrap()
                    ->sortable()
                    ->searchable(query: function ($query, string $search): void {
                        $map = [
                            'aktif' => 'active',
                            'dibuang' => 'removed',
                            'pindaan nama' => 'pindaan nama',
                            'batal nama' => 'batal nama',
                        ];

                        $lowerSearch = strtolower($search);

                        $query->where(function ($q) use ($search, $lowerSearch, $map): void {
                            $q->whereHas('pegawai', function ($pegawai) use ($search): void {
                                $pegawai->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nokp', 'like', "%{$search}%");
                            });

                            foreach ($map as $label => $value) {
                                if (str_contains($label, $lowerSearch)) {
                                    $q->orWhere('status', $value);

                                    return;
                                }
                            }
                        });
                    }),

                TextColumn::make('waran.no_waran')
                    ->label('No Waran / Butiran')
                    ->formatStateUsing(
                        fn ($record) => '<strong>'.e($record->waran?->no_waran).'</strong><br>'.e($record->butiran)
                    )
                    ->html()
                    ->wrap()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('warans as waran_sort', 'waran_jawatans.waran_id', '=', 'waran_sort.id')
                            ->orderBy('waran_sort.no_waran', $direction)
                            ->orderBy('waran_jawatans.butiran', $direction)
                            ->select('waran_jawatans.*');
                    })
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('butiran', 'like', "%{$search}%")
                                ->orWhereHas('waran', fn ($w) => $w->where('no_waran', 'like', "%{$search}%"));
                        });
                    }),

                TextColumn::make('aktiviti')
                    ->label('PTJ / Aktiviti / Jawatan')
                    ->getStateUsing(function ($record): string {
                        $muted = 'text-xs text-gray-500 dark:text-gray-400';

                        $ptj = e($record->ptj?->nama_ptj ?? '-');

                        $aktiviti = trim(
                            ($record->aktiviti?->no_aktivit ?? '').' - '.($record->aktiviti?->nama_aktiviti ?? ''),
                            ' -'
                        );
                        $aktiviti = $aktiviti !== '' ? e($aktiviti) : '-';

                        $jawatanParts = [];
                        if (filled($record->jawatan_list)) {
                            $jawatanParts[] = $record->jawatan_list;
                        }
                        if (filled($record->gred_list)) {
                            $jawatanParts[] = 'GRED '.$record->gred_list;
                        }

                        $jawatan = e($jawatanParts !== [] ? implode(' , ', $jawatanParts) : '-');

                        if (filled($record->butiran)) {
                            $jawatan .= ' ('.e($record->butiran).')';
                        }

                        return '<div class="font-medium">'.$ptj.'</div>'
                            .'<div class="'.$muted.'">'.$aktiviti.'</div>'
                            .'<div class="'.$muted.'">'.$jawatan.'</div>';
                    })
                    ->html()
                    ->wrap()
                    ->sortable(
                        query: function ($query, string $direction): void {
                            $query
                                ->leftJoin('ptjs as ptj_sort', 'waran_jawatans.ptj_id', '=', 'ptj_sort.id')
                                ->orderBy('ptj_sort.nama_ptj', $direction)
                                ->select('waran_jawatans.*');
                        }
                    )
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->whereHas('ptj', fn ($ptj) => $ptj->where('nama_ptj', 'like', "%{$search}%"))
                                ->orWhereHas('aktiviti', function ($aktiviti) use ($search): void {
                                    $aktiviti->where('no_aktivit', 'like', "%{$search}%")
                                        ->orWhere('nama_aktiviti', 'like', "%{$search}%");
                                })
                                ->orWhere('butiran', 'like', "%{$search}%");
                        });
                    }),

            ])
            ->filters([
                SelectFilter::make('waran_id')
                    ->label('No Waran')
                    ->relationship('waran', 'no_waran')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('butiran')
                    ->label('Butiran')
                    ->options(fn (): array => WaranJawatan::query()
                        ->listed()
                        ->whereNotNull('butiran')
                        ->where('butiran', '!=', '')
                        ->orderBy('butiran')
                        ->distinct()
                        ->pluck('butiran', 'butiran')
                        ->all())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('aktiviti')
                    ->label('Aktiviti')
                    ->relationship('aktiviti', 'nama_aktiviti')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->no_aktivit.' - '.$record->nama_aktiviti
                    )
                    ->searchable()
                    ->preload(),

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
                        ->label('Paparan')
                        ->modal()
                        ->color('info')
                        ->modalHeading(function ($record) {
                            if ($record->pegawai_id == null) {
                                return 'Tiada Penyandang';
                            } else {
                                return $record->pegawai?->nama;
                            }
                        })
                        ->extraModalWindowAttributes(fn (WaranJawatan $record): array => [
                            'class' => WaranJawatanInfolist::programWindowClass($record),
                        ])
                        ->extraModalFooterActions([
                            Action::make('edit')
                                ->label('Kemaskini')
                                ->url(fn ($record) => WaranJawatanResource::getUrl('edit', [
                                    'record' => $record,
                                ])),
                        ]),
                    EditAction::make(),
                    Action::make('removePegawai')
                        ->label('Buang Pegawai')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'pegawai_id' => null,
                            ]);
                        }),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn (Collection $records): mixed => static::notifyBlockedPegawai($records)),
                ]),
            ]);
    }
}
