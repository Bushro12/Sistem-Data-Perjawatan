<?php

namespace App\Filament\Resources\Units\Tables;

use App\Models\Bahagian;
use App\Models\Ptj;
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

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('bahagian.nama_bahagian')
                    ->label('PTJ / Bahagian')
                    ->getStateUsing(fn ($record) => '<strong>PTJ: '.e($record->bahagian?->ptj?->nama_ptj ?? '-').'</strong><br>BAHAGIAN: '.e($record->bahagian?->nama_bahagian ?? '-'))
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('bahagian', function (Builder $q) use ($search): void {
                            $q->where('nama_bahagian', 'like', "%{$search}%")
                                ->orWhereHas('ptj', function (Builder $p) use ($search): void {
                                    $p->where('nama_ptj', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('bahagians', 'units.bahagian_id', '=', 'bahagians.id')
                            ->leftJoin('ptjs', 'bahagians.ptj_id', '=', 'ptjs.id')
                            ->orderBy('ptjs.nama_ptj', $direction)
                            ->orderBy('bahagians.nama_bahagian', $direction)
                            ->select('units.*');
                    }),
                TextColumn::make('nama_unit')
                    ->label('Unit')
                    ->getStateUsing(function ($record): string {
                        static $cache = [];

                        $bid = $record->bahagian_id;

                        if (! isset($cache[$bid])) {
                            $cache[$bid] = Unit::where('bahagian_id', $bid)
                                ->orderBy('nama_unit')
                                ->pluck('nama_unit')
                                ->toArray();
                        }

                        return collect($cache[$bid])
                            ->map(fn (string $u): string => e($u))
                            ->implode('<br>');
                    })
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('nama_unit', 'like', "%{$search}%");
                    }),
            ])
            ->paginationPageOptions([5])
            ->defaultPaginationPageOption(5)
            // ->defaultSort(function (Builder $query): Builder {
            //     return $query
            //         ->leftJoin('bahagians', 'units.bahagian_id', '=', 'bahagians.id')
            //         ->leftJoin('ptjs', 'bahagians.ptj_id', '=', 'ptjs.id')
            //         ->orderBy('ptjs.nama_ptj')
            //         ->orderBy('bahagians.nama_bahagian')
            //         ->orderBy('units.nama_unit')
            //         ->select('units.*');
            // })
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(function (Builder $query): Builder {
                // Show one row per PTJ/Bahagian group - all units in same group aggregated in Unit cell
                return $query->whereIn('units.id', function ($q): void {
                    $q->selectRaw('MIN(id)')->from('units')->whereNull('deleted_at')->groupBy('bahagian_id');
                });
            })
            ->filters([
                Filter::make('ptj_bahagian')
                    ->label('PTJ / Bahagian')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->options(fn (): array => Ptj::pluck('nama_ptj', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('bahagian_id', null)),
                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->options(function (Get $get): array {
                                $ptjId = $get('ptj_id');

                                if (blank($ptjId)) {
                                    return [];
                                }

                                return Bahagian::where('ptj_id', $ptjId)
                                    ->pluck('nama_bahagian', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('ptj_id')))
                            ->helperText('Sila pilih PTJ dahulu'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ptj_id'] ?? null,
                                fn (Builder $q, $ptjId): Builder => $q->whereHas('bahagian', fn (Builder $b): Builder => $b->where('ptj_id', $ptjId))
                            )
                            ->when(
                                $data['bahagian_id'] ?? null,
                                fn (Builder $q, $bahagianId): Builder => $q->where('bahagian_id', $bahagianId)
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
                            $count = Unit::where('bahagian_id', $record->bahagian_id)->count();

                            return $count > 1 ? "Padam {$count} unit di {$record->bahagian?->nama_bahagian}?" : "Padam {$record->nama_unit}";
                        })
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record): void {
                            Unit::where('bahagian_id', $record->bahagian_id)->delete();
                        }),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $bahagianIds = $records->pluck('bahagian_id')->unique();
                            Unit::whereIn('bahagian_id', $bahagianIds)->delete();
                        }),
                ]),
            ]);
    }
}
