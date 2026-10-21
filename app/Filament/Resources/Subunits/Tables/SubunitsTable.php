<?php

namespace App\Filament\Resources\Subunits\Tables;

use App\Models\Program;
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
use Illuminate\Support\HtmlString;

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
                    ->label('Program / PTJ / Unit')
                    ->getStateUsing(function ($record): string {
                        $unit = $record->unit;
                        $ptj = $unit?->ptj;

                        $programs = $ptj?->programs
                            ?->pluck('nama_program')
                            ->filter()
                            ->implode(', ') ?: '-';

                        $muted = 'text-xs text-gray-500 dark:text-gray-400';

                        $html = '<div class="font-medium">PTJ: '.e($ptj?->nama_ptj ?? '-').'</div>';

                        $showBahagian = filled($unit?->bahagian_id)
                            || (bool) $ptj?->usesBahagianHierarchy();

                        if ($showBahagian) {
                            $html .= '<div class="'.$muted.'">BAHAGIAN: '.e($unit?->bahagian?->nama_bahagian ?? '-').'</div>';
                        }

                        $html .= '<div class="'.$muted.'">UNIT: '.e($unit?->nama_unit ?? '-').'</div>';

                        return $html;
                    })
                    ->html()
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('unit', function (Builder $q) use ($search): void {
                            $q->where('nama_unit', 'like', "%{$search}%")
                                ->orWhereHas('ptj', function (Builder $p) use ($search): void {
                                    $p->where('nama_ptj', 'like', "%{$search}%")
                                        ->orWhereHas('programs', function (Builder $pr) use ($search): void {
                                            $pr->where('nama_program', 'like', "%{$search}%");
                                        });
                                })
                                ->orWhereHas('bahagian', function (Builder $b) use ($search): void {
                                    $b->where('nama_bahagian', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('units', 'subunits.unit_id', '=', 'units.id')
                            ->leftJoin('ptjs', 'units.ptj_id', '=', 'ptjs.id')
                            ->orderBy('ptjs.nama_ptj', $direction)
                            ->orderBy('units.nama_unit', $direction)
                            ->select('subunits.*');
                    }),
                TextColumn::make('nama_subunit')
                    ->label('KD / KKIA / Wad / Klinik')
                    ->getStateUsing(fn ($record): array => static::subunitsForUnit($record))
                    ->wrap()
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('nama_subunit', 'like', "%{$search}%");
                    })
                    ->action(
                        Action::make('lihatSemuaSubunit')
                            ->modalHeading('Senarai KD / KKIA / Wad / Klinik')
                            ->modalDescription(function ($record): string {
                                $unit = $record->unit;
                                $parts = [
                                    $unit?->ptj?->nama_ptj,
                                    $unit?->bahagian?->nama_bahagian,
                                    $unit?->nama_unit,
                                ];

                                return collect($parts)->filter()->implode(' - ') ?: '-';
                            })
                            ->modalContent(fn ($record): HtmlString => static::listModalContent(
                                static::subunitsForUnit($record)
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->disabled(fn ($record): bool => count(static::subunitsForUnit($record)) <= 3)
                    ),
            ])
            ->paginationPageOptions([5])
            ->defaultPaginationPageOption(5)
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['unit.ptj.programs', 'unit.bahagian'])
                    ->whereIn('subunits.id', function ($q): void {
                        $q->selectRaw('MIN(id)')->from('subunits')->groupBy('unit_id');
                    });
            })
            ->filters([
                Filter::make('program_ptj_unit')
                    ->label('Program / PTJ / Unit')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program')
                            ->options(fn (): array => Program::query()->orderBy('nama_program')->pluck('nama_program', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('ptj_id', null);
                                $set('unit_id', null);
                            }),
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->options(function (Get $get): array {
                                $programId = $get('program_id');

                                $query = Ptj::query()->orderBy('nama_ptj');

                                if (filled($programId)) {
                                    $query->whereHas('programs', fn ($q) => $q->whereKey($programId));
                                }

                                return $query->pluck('nama_ptj', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('unit_id', null)),
                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(function (Get $get): array {
                                $ptjId = $get('ptj_id');

                                if (blank($ptjId)) {
                                    return [];
                                }

                                return Unit::where('ptj_id', $ptjId)
                                    ->orderBy('nama_unit')
                                    ->pluck('nama_unit', 'id')
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
                    $data['program_id'] ?? null,
                    fn (Builder $q, $programId): Builder => $q->whereRelation('unit.ptj.programs', 'programs.id', $programId)
                )
                ->when(
                    $data['ptj_id'] ?? null,
                    fn (Builder $q, $ptjId): Builder => $q->whereIn('subunits.unit_id', function ($u) use ($ptjId) {
                        $u->select('id')->from('units')->where('ptj_id', $ptjId);
                    })
                )
                ->when(
                    $data['unit_id'] ?? null,
                    fn (Builder $q, $unitId): Builder => $q->where('subunits.unit_id', $unitId)
                );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['program_id'] ?? null)) {
                            $program = Program::find($data['program_id']);
                            $indicators[] = 'Program: '.($program?->nama_program ?? $data['program_id']);
                        }

                        if (filled($data['ptj_id'] ?? null)) {
                            $ptj = Ptj::find($data['ptj_id']);
                            $indicators[] = 'PTJ: '.($ptj?->nama_ptj ?? $data['ptj_id']);
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

                            return $count > 1 ? "Padam {$count} KD / KKIA / Wad / Klinik di {$record->unit?->nama_unit}?" : "Padam {$record->nama_subunit}";
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

    /**
     * @return array<int, string>
     */
    protected static function subunitsForUnit($record): array
    {
        static $cache = [];

        $unitId = $record->unit_id;

        if (! isset($cache[$unitId])) {
            $cache[$unitId] = Subunit::query()
                ->where('unit_id', $unitId)
                ->orderBy('nama_subunit')
                ->pluck('nama_subunit')
                ->filter()
                ->values()
                ->all();
        }

        return $cache[$unitId];
    }

    /**
     * @param  array<int, string>  $items
     */
    protected static function listModalContent(array $items): HtmlString
    {
        if ($items === []) {
            return new HtmlString('<p class="text-sm text-gray-500">Tiada rekod.</p>');
        }

        $lis = collect($items)
            ->map(fn (string $item): string => '<li>'.e($item).'</li>')
            ->implode('');

        return new HtmlString('<ul class="list-disc space-y-1 pl-5 text-sm">'.$lis.'</ul>');
    }
}