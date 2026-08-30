<?php

namespace App\Filament\Resources\Bahagians\Tables;

use App\Models\Bahagian;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BahagiansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('ptj.nama_ptj')
                    ->label('PTJ')
                    ->wrap()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('ptjs', 'bahagians.ptj_id', '=', 'ptjs.id')
                            ->orderBy('ptjs.nama_ptj', $direction)
                            ->select('bahagians.*');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->whereHas('ptj', fn (Builder $p) => $p->where('nama_ptj', 'like', "%{$search}%"))
                                ->orWhereExists(function ($sub) use ($search): void {
                                    $sub->selectRaw('1')
                                        ->from('bahagians as b2')
                                        ->whereColumn('b2.ptj_id', 'bahagians.ptj_id')
                                        ->where('b2.nama_bahagian', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('nama_bahagian')
                    ->label('Bahagian')
                    ->getStateUsing(function ($record): string {
                        static $cache = [];

                        $ptjId = $record->ptj_id;

                        if (! isset($cache[$ptjId])) {
                            $cache[$ptjId] = Bahagian::where('ptj_id', $ptjId)
                                ->orderBy('nama_bahagian')
                                ->pluck('nama_bahagian')
                                ->toArray();
                        }

                        return collect($cache[$ptjId])
                            ->map(fn (string $b): string => e($b))
                            ->implode('<br>');
                    })
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->whereHas('ptj', fn (Builder $p) => $p->where('nama_ptj', 'like', "%{$search}%"))
                                ->orWhereExists(function ($sub) use ($search): void {
                                    $sub->selectRaw('1')
                                        ->from('bahagians as b2')
                                        ->whereColumn('b2.ptj_id', 'bahagians.ptj_id')
                                        ->where('b2.nama_bahagian', 'like', "%{$search}%");
                                });
                        });
                    }),
            ])
            ->paginationPageOptions([5])
            ->defaultPaginationPageOption(5)
            ->defaultSort(function (Builder $query): Builder {
                // Most recently created/updated PTJ group at top - uses MAX(updated_at) per PTJ
                return $query->orderByDesc(DB::raw('(SELECT MAX(b2.updated_at) FROM bahagians b2 WHERE b2.ptj_id = bahagians.ptj_id)'));
            })
            ->modifyQueryUsing(function (Builder $query): Builder {
                // One row per PTJ - all bahagian for same PTJ shown together in one cell
                return $query->whereIn('bahagians.id', function ($q): void {
                    $q->selectRaw('MIN(id)')->from('bahagians')->whereNull('deleted_at')->groupBy('ptj_id');
                });
            })
            ->filters([
                SelectFilter::make('ptj_id')
                    ->label('PTJ')
                    ->relationship('ptj', 'nama_ptj')
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    // ViewAction::make(),
                    EditAction::make()
                        // ->modal()
                        ->modalHeading('Kemaskini Bahagian')
                        ->modalSubmitActionLabel('Simpan')
                        ->modalCancelActionLabel('Batal'),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(function ($record): string {
                            $count = Bahagian::where('ptj_id', $record->ptj_id)->count();

                            return $count > 1 ? "Padam {$count} bahagian di {$record->ptj?->nama_ptj}?" : "Padam {$record->nama_bahagian}";
                        })
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record): void {
                            Bahagian::where('ptj_id', $record->ptj_id)->delete();
                        }),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $ptjIds = $records->pluck('ptj_id')->unique();
                            Bahagian::whereIn('ptj_id', $ptjIds)->delete();
                        }),
                ]),
            ]);
    }
}
