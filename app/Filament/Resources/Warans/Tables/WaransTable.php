<?php

namespace App\Filament\Resources\Warans\Tables;

use App\Filament\Support\BlockedPegawaiDelete;
use App\Models\Aktiviti;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\User;
use App\Models\Waran;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class WaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->recordUrl(fn ($record) => route('filament.app.resources.warans.view', [
                'record' => $record,
            ]))

            ->modifyQueryUsing(
                fn ($query) => $query->with([
                    'waranJawatan.ptj',
                    'waranJawatan.aktiviti',
                ])
            )

            ->columns([

                // Bil
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),

                // Waran Info
                TextColumn::make('no_waran')
                    ->label('Maklumat Waran')
                    ->searchable()
                    ->sortable(),

                // TextColumn::make('butiran_list')
                //     ->label('Butiran')
                //     ->html()
                //     ->searchable(query: function ($query, $search) {
                //         $query->whereHas('waranJawatan', function ($q) use ($search) {
                //             $q->where('butiran', 'like', "%{$search}%");
                //         });

                //     }),
                // Aktiviti (unique, no repeat)
                TextColumn::make('aktiviti_list')
                    ->label('Aktiviti')
                    // ->formatStateUsing(function ($record) {

                    //     $items = $record->jenis === 'Tolak'
                    //         ? WaranJawatan::withTrashed()
                    //             ->where('waran_tolak_id', $record->id)
                    //             ->with('aktiviti')
                    //             ->get()
                    //         : $record->waranJawatan;

                    //     return $items
                    //         ->map(
                    //             fn($wj) =>
                    //             $wj->aktiviti
                    //             ? $wj->aktiviti->no_aktivit . ' - ' . $wj->aktiviti->nama_aktiviti
                    //             : null
                    //         )
                    //         ->filter()
                    //         ->unique()
                    //         ->join('<br>');
                    // })
                    ->html()
                    ->wrap()
                    // ->sortable()
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('waranJawatan.aktiviti', function ($q) use ($search) {
                            $q->where('nama_aktiviti', 'like', "%{$search}%")
                                ->orWhere('no_aktivit', 'like', "%{$search}%");
                        })
                            ->orWhereHas('waranJawatan', function ($q) use ($search) {
                                $q->whereHas('aktiviti', function ($q2) use ($search) {
                                    $q2->where('nama_aktiviti', 'like', "%{$search}%")
                                        ->orWhere('no_aktivit', 'like', "%{$search}%");
                                });
                            });
                    }),

                // TextColumn::make('aktiviti_list')
                //     ->label('Aktiviti')
                //     ->html()
                //     ->formatStateUsing(function ($record) {

                //         return WaranJawatan::query()
                //             ->where('waran_id', $record->id)
                //             ->whereHas('aktiviti', function ($q) use ($record) {
                //                 $q->where('program_id', $record->grouped_program_id);
                //             })
                //             ->with('aktiviti')
                //             ->get()

                //             ->map(
                //                 fn($wj) =>
                //                 $wj->aktiviti
                //                 ? $wj->aktiviti->no_aktivit . ' - ' . $wj->aktiviti->nama_aktiviti
                //                 : null
                //             )
                //             ->filter()
                //             ->unique()
                //             ->join('<br>');

                //     }),

                TextColumn::make('penempatan_list')
                    ->label('Penempatan')
                    ->html()
                    ->wrap()
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('waranJawatan.ptj', function ($q) use ($search) {
                            $q->where('nama_ptj', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('jik_count')
                    ->label('J')
                    ->formatStateUsing(function ($state, $record) {

                        return $record->jenis === 'Tolak'
                            ? '-'.$state
                            : '+'.$state;
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

                            $record->jenis === 'Tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis === 'Tolak' && $record->status_jik === 'Kurang' => 'warning',

                            $record->jenis !== 'Tolak' && $record->status_jik === 'Lebih' => 'danger',
                            $record->jenis !== 'Tolak' && $record->status_jik === 'Kurang' => 'warning',

                            default => 'gray',
                        };
                    })
                    ->searchable(query: function ($query, $search) {

                        $query->where(function ($q) use ($search) {

                            $countSub = '(SELECT COUNT(*) FROM waran_jawatans WHERE waran_jawatans.waran_id = warans.id)';

                            if (str_contains(strtolower($search), 'seimbang')) {
                                $q->orWhereRaw("jik = $countSub");
                            }

                            if (str_contains(strtolower($search), 'kurang')) {
                                $q->orWhereRaw("jik > $countSub");
                            }

                            if (str_contains(strtolower($search), 'lebih')) {
                                $q->orWhereRaw("jik < $countSub");
                            }

                        });

                    }),
            ])

            ->filters([
                Filter::make('program_aktiviti')
                    ->label('Program / Aktiviti / PTJ')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program')
                            ->options(fn (): array => Program::query()
                                ->orderBy('nama_program')
                                ->pluck('nama_program', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('aktiviti_id', null);
                                $set('ptj_id', null);
                            }),
                        Select::make('aktiviti_id')
                            ->label('Aktiviti')
                            ->options(function (Get $get): array {
                                $programId = $get('program_id');

                                if (blank($programId)) {
                                    return [];
                                }

                                return Aktiviti::query()
                                    ->where('program_id', $programId)
                                    ->orderBy('no_aktivit')
                                    ->get()
                                    ->mapWithKeys(fn (Aktiviti $aktiviti): array => [
                                        $aktiviti->id => $aktiviti->no_aktivit.' - '.$aktiviti->nama_aktiviti,
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (Get $get): bool => blank($get('program_id')))
                            ->helperText('Sila pilih Program dahulu')
                            ->afterStateUpdated(fn (Set $set) => $set('ptj_id', null)),
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->options(function (Get $get): array {
                                $aktivitiId = $get('aktiviti_id');

                                if (filled($aktivitiId)) {
                                    return Aktiviti::ptjSelectOptionsFor($aktivitiId);
                                }

                                $programId = $get('program_id');
                                $query = Ptj::query()->orderBy('nama_ptj');

                                if (filled($programId)) {
                                    $query->whereHas('programs', fn ($q) => $q->whereKey($programId));
                                }

                                return $query->pluck('nama_ptj', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['program_id'] ?? null,
                                fn (Builder $q, $programId): Builder => $q->whereHas(
                                    'waranJawatan.aktiviti',
                                    fn (Builder $aktiviti): Builder => $aktiviti->where('program_id', $programId)
                                )
                            )
                            ->when(
                                $data['aktiviti_id'] ?? null,
                                fn (Builder $q, $aktivitiId): Builder => $q->whereHas(
                                    'waranJawatan',
                                    fn (Builder $waranJawatan): Builder => $waranJawatan->where('aktiviti_id', $aktivitiId)
                                )
                            )
                            ->when(
                                $data['ptj_id'] ?? null,
                                fn (Builder $q, $ptjId): Builder => $q->whereHas(
                                    'waranJawatan',
                                    fn (Builder $waranJawatan): Builder => $waranJawatan->where('ptj_id', $ptjId)
                                )
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['program_id'] ?? null)) {
                            $program = Program::find($data['program_id']);
                            $indicators[] = 'Program: '.($program?->nama_program ?? $data['program_id']);
                        }

                        if (filled($data['aktiviti_id'] ?? null)) {
                            $aktiviti = Aktiviti::find($data['aktiviti_id']);
                            $indicators[] = 'Aktiviti: '.($aktiviti
                                ? $aktiviti->no_aktivit.' - '.$aktiviti->nama_aktiviti
                                : $data['aktiviti_id']);
                        }

                        if (filled($data['ptj_id'] ?? null)) {
                            $ptj = Ptj::find($data['ptj_id']);
                            $indicators[] = 'PTJ: '.($ptj?->nama_ptj ?? $data['ptj_id']);
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Kemaskini'),
                    DeleteAction::make()
                        ->label('Padam')
                        ->before(function (DeleteAction $action, Waran $record): void {
                            BlockedPegawaiDelete::haltIfAssigned($action, $record->hasAssignedPegawai());
                        })
                        ->modalHeading(fn ($record) => "Padam {$record->no_waran}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->after(function ($record) {
                            Log::info('Waran Deleted', [
                                'waran_id' => $record->id,
                                'user_id' => auth()->id(),
                            ]);

                            $creator = auth()->user();

                            $no_waran = $record->no_waran;

                            $superadmin = User::whereIn('role', [1, 2])->get();

                            Notification::make()
                                ->title('Waran Dipadam')
                                ->body("Waran {$no_waran} telah dipadam oleh {$creator->name}")
                                ->danger()
                                ->sendToDatabase($superadmin);

                        }),

                ])
                    ->tooltip('Tindakan'),
            ]);
    }
}
