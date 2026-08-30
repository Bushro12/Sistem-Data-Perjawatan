<?php

namespace App\Filament\Resources\Subunits\Tables;

use App\Models\Bahagian;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubunitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('unit.nama_unit')
                    ->label('PTJ / Bahagian / Unit')
                    ->getStateUsing(fn ($record) => '<strong>PTJ: '.e($record->unit?->bahagian?->ptj?->nama_ptj ?? '-').'</strong><br>BAHAGIAN: '.e($record->unit?->bahagian?->nama_bahagian ?? '-').'<br>UNIT: '.e($record->unit?->nama_unit ?? '-'))
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('unit', function (Builder $q) use ($search): void {
                            $q->where('nama_unit', 'like', "%{$search}%")
                                ->orWhereHas('bahagian', function (Builder $b) use ($search): void {
                                    $b->where('nama_bahagian', 'like', "%{$search}%")
                                        ->orWhereHas('ptj', function (Builder $p) use ($search): void {
                                            $p->where('nama_ptj', 'like', "%{$search}%");
                                        });
                                });
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('units', 'subunits.unit_id', '=', 'units.id')
                            ->leftJoin('bahagians', 'units.bahagian_id', '=', 'bahagians.id')
                            ->leftJoin('ptjs', 'bahagians.ptj_id', '=', 'ptjs.id')
                            ->orderBy('ptjs.nama_ptj', $direction)
                            ->orderBy('bahagians.nama_bahagian', $direction)
                            ->orderBy('units.nama_unit', $direction)
                            ->select('subunits.*');
                    }),
                TextColumn::make('nama_subunit')
                    ->label('Sub Unit')
                    ->getStateUsing(function ($record): string {
                        static $cache = [];

                        $unitId = $record->unit_id;

                        if (! isset($cache[$unitId])) {
                            $cache[$unitId] = Subunit::where('unit_id', $unitId)
                                ->orderBy('nama_subunit')
                                ->pluck('nama_subunit')
                                ->toArray();
                        }

                        return collect($cache[$unitId])
                            ->map(fn (string $s): string => e($s))
                            ->implode('<br>');
                    })
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('nama_subunit', 'like', "%{$search}%");
                    }),
            ])
            ->paginationPageOptions([5])
            ->defaultPaginationPageOption(5)
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->leftJoin('units', 'subunits.unit_id', '=', 'units.id')
                    ->leftJoin('bahagians', 'units.bahagian_id', '=', 'bahagians.id')
                    ->leftJoin('ptjs', 'bahagians.ptj_id', '=', 'ptjs.id')
                    ->orderBy('ptjs.nama_ptj')
                    ->orderBy('bahagians.nama_bahagian')
                    ->orderBy('units.nama_unit')
                    ->select('subunits.*');
            })
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->whereIn('subunits.id', function ($q): void {
                    $q->selectRaw('MIN(id)')->from('subunits')->groupBy('unit_id');
                });
            })
            ->filters([
                Filter::make('ptj_bahagian_unit')
                    ->label('PTJ / Bahagian / Unit')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->options(fn (): array => Ptj::query()->orderBy('nama_ptj')->pluck('nama_ptj', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('bahagian_id', null);
                                $set('unit_id', null);
                            }),
                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->options(function (Get $get): array {
                                $ptjId = $get('ptj_id');

                                if (blank($ptjId)) {
                                    return [];
                                }

                                return Bahagian::where('ptj_id', $ptjId)
                                    ->orderBy('nama_bahagian')
                                    ->pluck('nama_bahagian', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('ptj_id')))
                            ->helperText('Sila pilih PTJ dahulu')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('unit_id', null)),
                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(function (Get $get): array {
                                $bahagianId = $get('bahagian_id');

                                if (blank($bahagianId)) {
                                    return [];
                                }

                                return Unit::where('bahagian_id', $bahagianId)
                                    ->orderBy('nama_unit')
                                    ->pluck('nama_unit', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('bahagian_id')))
                            ->helperText('Sila pilih Bahagian dahulu'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ptj_id'] ?? null,
                                fn (Builder $q, $ptjId): Builder => $q->whereHas('unit.bahagian', fn (Builder $b): Builder => $b->where('ptj_id', $ptjId))
                            )
                            ->when(
                                $data['bahagian_id'] ?? null,
                                fn (Builder $q, $bahagianId): Builder => $q->whereHas('unit', fn (Builder $u): Builder => $u->where('bahagian_id', $bahagianId))
                            )
                            ->when(
                                $data['unit_id'] ?? null,
                                fn (Builder $q, $unitId): Builder => $q->where('unit_id', $unitId)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['ptj_id'] ?? null)) {
                            $ptj = Ptj::find($data['ptj_id']);
                            $indicators[] = 'PTJ: '.($ptj?->nama_ptj ?? $data['ptj_id']);
                        }

                        if (filled($data['bahagian_id'] ?? null)) {
                            $bahagian = Bahagian::find($data['bahagian_id']);
                            $indicators[] = 'Bahagian: '.($bahagian?->nama_bahagian ?? $data['bahagian_id']);
                        }

                        if (filled($data['unit_id'] ?? null)) {
                            $unit = Unit::find($data['unit_id']);
                            $indicators[] = 'Unit: '.($unit?->nama_unit ?? $data['unit_id']);
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(function ($record): string {
                            $count = Subunit::where('unit_id', $record->unit_id)->count();

                            return $count > 1 ? "Padam {$count} sub unit di {$record->unit?->nama_unit}?" : "Padam {$record->nama_subunit}";
                        })
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record): void {
                            Subunit::where('unit_id', $record->unit_id)->delete();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $unitIds = $records->pluck('unit_id')->unique();
                            Subunit::whereIn('unit_id', $unitIds)->delete();
                        }),
                ]),
            ]);
    }
}
