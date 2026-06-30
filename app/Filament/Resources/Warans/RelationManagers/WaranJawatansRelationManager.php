<?php

namespace App\Filament\Resources\Warans\RelationManagers;

use App\Filament\Resources\WaranJawatans\WaranJawatanResource;
use App\Filament\Resources\Warans\WaranResource;
use App\Models\Bahagian;
use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use App\Models\User;
use App\Models\WaranJawatan;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Reactive;
use Filament\Infolists\Infolist;

class WaranJawatansRelationManager extends RelationManager
{
    protected static string $relationship = 'waranJawatan';

    protected static ?string $title = 'Penempatan';
    public ?int $aktivitiFilter = null;
    public ?string $butiranFilter = null;

    public string $viewMode = 'active';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Maklumat Waran')
                            ->schema([
                                Select::make('aktiviti_id')
                                    ->required()
                                    ->options(function () {

                                        return Program::with('aktiviti')
                                            ->orderBy('nama_program')
                                            ->get()
                                            ->mapWithKeys(function ($program) {

                                                return [
                                                    $program->nama_program => $program->aktiviti
                                                        ->mapWithKeys(function ($aktiviti) {
                                                            return [
                                                                $aktiviti->id => $aktiviti->no_aktivit . ' - ' . $aktiviti->nama_aktiviti
                                                            ];
                                                        })
                                                        ->toArray(),
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->columns(1)
                                    ->disabled(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),

                                TextInput::make('butiran')
                                    ->required()
                                    ->maxLength(255)
                                    ->readonly(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),

                                Select::make('jawatan_ids')
                                    ->label('Jawatan')
                                    ->multiple()
                                    ->options(
                                        Jawatan::orderBy('desc_jawatan')
                                            ->pluck('desc_jawatan', 'id')
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),
                                Select::make('gred_ids')
                                    ->label('Gred')
                                    ->multiple()
                                    ->options(function (Get $get) {

                                        $jawatanIds = $get('jawatan_ids');

                                        if (blank($jawatanIds)) {
                                            return [];
                                        }

                                        return Jawatan_Gred::query()
                                            ->whereIn('jawatan_id', $jawatanIds)
                                            ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                                            ->orderBy('greds.kod_gred')
                                            ->pluck('greds.kod_gred', 'greds.id')
                                            ->toArray();
                                    })
                                    ->disabled(fn(Get $get) => blank($get('jawatan_ids')))
                                    ->searchable()
                                    ->preload()
                                    ->multiple()
                                    ->live(),

                                Select::make('ptj_id')
                                    ->label('PTJ')
                                    ->options(
                                        Ptj::pluck('nama_ptj', 'id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull()
                                    ->disabled(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),

                                Select::make('bahagian_id')
                                    ->label('Bahagian')
                                    ->options(function (Get $get) {

                                        $ptjId = $get('ptj_id');

                                        if (blank($ptjId)) {
                                            return [];
                                        }

                                        return Bahagian::query()
                                            ->where('ptj_id', $ptjId)
                                            ->orderBy('nama_bahagian')
                                            ->pluck('nama_bahagian', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(fn(Get $get) => blank($get('ptj_id')))
                                    ->columnSpanFull(),

                                Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(function (Get $get) {
                                        $bahagianId = $get('bahagian_id');

                                        if (blank($bahagianId)) {
                                            return [];
                                        }

                                        return Unit::query()
                                            ->where('bahagian_id', $bahagianId)
                                            ->orderBy('nama_unit')
                                            ->pluck('nama_unit', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->preload()
                                    ->disabled(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),

                                Select::make('subunit_id')
                                    ->label('Subunit')
                                    ->options(function (Get $get) {
                                        $unitId = $get('unit_id');

                                        if (blank($unitId)) {
                                            return [];
                                        }

                                        return Subunit::query()
                                            ->where('unit_id', $unitId)
                                            ->orderBy('nama_subunit')
                                            ->pluck('nama_subunit', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(
                                        fn() =>
                                        !auth()->user()?->isSuperadmin()
                                        && !auth()->user()?->isAdmin()
                                    ),

                                Select::make('status')
                                    ->required()
                                    ->label('Status')
                                    ->default('active')
                                    ->options([
                                        'active' => 'Aktif',
                                        'pindaan nama' => 'Pindaan Nama',
                                        'batal nama' => 'Batal Nama',
                                        'removed' => 'Buang Jawatan'
                                    ])
                                    ->searchable()
                                    ->preload(),

                            ]),

                        Tab::make('Nama Penyandang')
                            ->schema([
                                Select::make('pegawai_id')
                                    ->label('Pegawai')
                                    ->options(function (Get $get, ?WaranJawatan $record) {

                                        $jawatanIds = $get('jawatan_ids');
                                        $gredIds = $get('gred_ids');

                                        if (blank($jawatanIds) || blank($gredIds)) {
                                            return [];
                                        }

                                        $jawatanGredIds = Jawatan_Gred::query()
                                            ->whereIn('jawatan_id', $jawatanIds)
                                            ->whereIn('gred_id', $gredIds)
                                            ->pluck('id');

                                        return Pegawai::query()
                                            ->whereIn('jawatan_gred_id', $jawatanGredIds)
                                            ->where(function ($query) use ($record) {

                                                $query->whereNotIn('id', function ($q) use ($record) {

                                                    $q->select('pegawai_id')
                                                        ->from('waran_jawatans')
                                                        ->whereNotNull('pegawai_id')
                                                        ->where('status', 'active') // ✅ only active assignments
                                                        ->whereNull('deleted_at');  // ✅ ignore soft deleted

                                                    // exclude current record if editing
                                                    if ($record) {
                                                        $q->where('id', '!=', $record->id);
                                                    }
                                                });

                                                // keep currently selected pegawai visible in dropdown
                                                if ($record?->pegawai_id) {
                                                    $query->orWhere('id', $record->pegawai_id);
                                                }
                                            })
                                            ->orderBy('nama')
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->preload()
                                    ->columnSpanFull()
                                ,


                                Checkbox::make('is_kup')
                                    ->label('Khas Untuk Penyandang (KUP)'),

                                Textarea::make('catatan_jawatan')
                                    ->label('Catatan')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }


    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('ptj')

            ->modifyQueryUsing(function (Builder $query) {

                $waran = $this->getOwnerRecord();
                $user = auth()->user();

                $query = WaranJawatan::query()->withoutGlobalScopes();
                if ($user?->role == 3) {
                    $query->where('ptj_id', $user->ptj_id);
                }

                if ($waran->jenis !== 'Tolak') {

                    return $query
                        ->where('waran_id', $waran->id)
                        ->where('status', '!=', 'deleted')
                        ->orderByRaw("status = 'removed' ASC")
                        ->orderBy('id', 'asc');
                }

                if ($this->viewMode === 'active') {
                    return $query->whereNull('deleted_at')
                        ->where('status', '!=', 'deleted');
                }

                if ($this->viewMode === 'inactive') {
                    return $query
                        ->where('waran_tolak_id', $waran->id)
                        ->whereNotNull('deleted_at')
                        ->where('status', 'removed');
                }

                return $query;
            })


            ->columns([

                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),
                TextColumn::make('butiran')
                    ->label('Butiran')
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),
                TextColumn::make('aktiviti')
                    ->label('Aktiviti')
                    ->formatStateUsing(function ($record) {
                        return $record->aktiviti
                            ? $record->aktiviti->no_aktivit . ' - ' . $record->aktiviti->nama_aktiviti
                            : '-';
                    })
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    )
                    ->wrap(),
                TextColumn::make('jawatan_gred_display')
                    ->label('Jawatan / Gred')
                    ->state(function ($record) {
                        return $record->jawatan_list . '<br>' . $record->gred_list;
                    })
                    ->html()
                    ->wrap()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),

                TextColumn::make('ptj.nama_ptj')
                    ->searchable()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    )
                    ->wrap(),
                // TextColumn::make('pegawai.nama')
                //     ->label('Nama Penyandang')
                //     ->searchable()
                //     ->color(
                //         fn($record) =>
                //         $record->status === 'removed' ? 'gray' : 'default'
                //     )
                //     ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->size('lg')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'removed' => 'Dibuang',
                        'pindaan nama' => 'Pindaan Nama',
                        'batal nama' => 'Batal Nama',
                        default => 'Aktif',
                    })
                    ->color(
                        fn($state) => match ($state) {
                            'removed' => 'danger',
                            'pindaan nama' => 'info',
                            'batal nama' => 'primary',
                            default => 'success',
                        }
                    )
            ])

            ->filters([

                // TrashedFilter::make(),

                SelectFilter::make('aktiviti_id')
                    ->label('Aktiviti')
                    ->options(function () {

                        return Program::with('aktiviti')
                            ->orderBy('nama_program')
                            ->get()
                            ->mapWithKeys(function ($program) {

                                return [
                                    $program->nama_program => $program->aktiviti
                                        ->mapWithKeys(function ($aktiviti) {
                                            return [
                                                $aktiviti->id =>
                                                    $aktiviti->no_aktivit . ' - ' . $aktiviti->nama_aktiviti
                                            ];
                                        })
                                        ->toArray(),
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'Tolak'),

                SelectFilter::make('butiran')
                    ->label('Butiran')
                    ->options(
                        WaranJawatan::query()
                            ->pluck('butiran', 'butiran')
                            ->unique()
                            ->toArray()
                    )
                    ->searchable()
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'Tolak'),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->filtersApplyAction(
                fn(Action $action) => $action->label('Cari Jawatan')
            )

            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Jawatan')
                    // ->modalHeading('Tambah Jawatan')
                    // ->modalSubmitActionLabel('Tambah')
                    // ->modalCancelActionLabel('Batal')
                    ->createAnother(false)
                    ->visible(
                        fn() =>
                        $this->getOwnerRecord()?->jenis === 'Tambah'
                        && (auth()->user()?->isSuperadmin() || auth()->user()?->isAdmin())
                    )
                    ->after(function ($record) {
                        Log::info('Penempatan Waran Added', [
                            'waran_jawatan_id' => $record->id,
                            'user_id' => auth()->id(),
                        ]);
                        $waranJawatan = $record;

                        $noWaran = $this->getOwnerRecord()->no_waran;

                        $recipients = User::where('role', 3)
                            ->where('ptj_id', $waranJawatan->ptj_id)
                            ->get();

                        Notification::make()
                            ->title('Waran Diterima')
                            ->body("Waran {$noWaran} telah diterima")
                            ->success()
                            ->actions([
                                Action::make('view')
                                    ->label('Lihat Waran')
                                    ->url(
                                        WaranResource::getUrl('edit', [
                                            'record' => $waranJawatan->waran_id,
                                        ])
                                    )
                                    ->markAsRead(),
                            ])
                            ->sendToDatabase($recipients);
                    }),

                Action::make('viewModeTabs')
                    ->label('')
                    ->view('filament.custom.warans.view-mode-tabs', [
                        'viewMode' => fn($livewire) => $livewire->viewMode,
                    ])
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'Tolak'),

            ])

            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => $record->status !== 'removed'),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->actions([
                ActionGroup::make([

                    ViewAction::make('view')
                        ->label('Paparan')
                        ->color('info')
                        // ->url(fn($record) => WaranJawatanResource::getUrl('view', [
                        //     'record' => $record,
                        // ])),
                        ->modalHeading(function ($record) {
                            return $record->waran?->no_waran . ' - ' . $record->ptj?->nama_ptj;
                        })
                        ->modalCloseButton(false)
                        ->infolist([
                            Grid::make(2)
                                ->schema([
                                    Tabs::make('Tabs')
                                        ->tabs([
                                            Tab::make('Maklumat Waran')
                                                ->schema([
                                                    TextEntry::make('waran.no_waran')
                                                        ->label('No Waran'),
                                                    TextEntry::make('butiran'),
                                                    TextEntry::make('aktiviti_id')
                                                        ->label('Aktiviti')
                                                        ->formatStateUsing(function ($record) {
                                                            return $record->aktiviti
                                                                ? $record->aktiviti->no_aktivit . ' - ' . $record->aktiviti->nama_aktiviti
                                                                : '-';
                                                        }),

                                                    TextEntry::make('jawatan_gred_display')
                                                        ->label('Jawatan / Gred')
                                                        ->state(function ($record) {
                                                            return $record->jawatan_list . ' , GRED ' . $record->gred_list;
                                                        })
                                                        ->html(),

                                                    TextEntry::make('ptj.nama_ptj')
                                                        ->label('PTJ'),
                                                    TextEntry::make('bahagian.nama_bahagian')
                                                        ->label('Bahagian')
                                                        ->state(function ($record) {
                                                            if ($record->bahagian_id == null) {
                                                                return 'Tiada';
                                                            } else {
                                                                return $record->bahagian?->nama_bahagian;
                                                            }
                                                        }),
                                                    TextEntry::make('unit.nama_unit')
                                                        ->label('Unit')
                                                        ->state(function ($record) {
                                                            if ($record->unit_id == null) {
                                                                return 'Tiada';
                                                            } else {
                                                                return $record->unit?->nama_unit;
                                                            }
                                                        }),
                                                    TextEntry::make('subunit.nama_subunit')
                                                        ->label('Sub Unit')
                                                        ->state(function ($record) {
                                                            if ($record->subunit_id == null) {
                                                                return 'Tiada';
                                                            } else {
                                                                return $record->subunit?->nama_subunit;
                                                            }
                                                        }),

                                                    TextEntry::make('status')
                                                        ->label('Status')
                                                        ->badge()
                                                        ->size('lg')
                                                        ->formatStateUsing(fn($state) => match ($state) {
                                                            'removed' => 'Dibuang',
                                                            'pindaan nama' => 'Pindaan Nama',
                                                            'batal nama' => 'Batal Nama',
                                                            default => 'Aktif',
                                                        })
                                                        ->color(
                                                            fn($state) => match ($state) {
                                                                'removed' => 'danger',
                                                                'pindaan nama' => 'info',
                                                                'batal nama' => 'primary',
                                                                default => 'success',
                                                            }
                                                        ),
                                                ]),

                                            Tab::make('Nama Penyandang')
                                                ->schema([
                                                    TextEntry::make('pegawai.nama')
                                                        ->label('Nama Pegawai')
                                                        ->columnSpanFull()
                                                        ->state(function ($record) {
                                                            if ($record->pegawai_id == null) {
                                                                return 'Tiada Penyandang';
                                                            } else {
                                                                return $record->pegawai?->nama;
                                                            }
                                                        }),

                                                    TextEntry::make('is_kup')
                                                        ->label('Lain-Lain')
                                                        ->state(function ($record) {
                                                            if ($record->is_kup == 0) {
                                                                return 'Tiada';
                                                            } else {
                                                                return 'Khas Untuk Penyandang';
                                                            }
                                                        }),

                                                    TextEntry::make('catatan_jawatan')
                                                        ->label('Catatan')
                                                        ->columnSpanFull()
                                                        ->state(function ($record) {
                                                            if ($record->catatan_jawatan == null) {
                                                                return 'Tiada';
                                                            } else {
                                                                return $record->catatan_jawatan;
                                                            }
                                                        })
                                                ])
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull()
                                ])


                        ])
                        ->extraModalFooterActions([

                            EditAction::make()
                                ->label('Edit')
                                ->visible(fn() => $this->getOwnerRecord()->jenis === 'Tambah')
                                ->modalCancelActionLabel('Batal')
                                ->modalSubmitAction(
                                    fn($action) => $action
                                        ->label('Simpan')
                                        ->color('primary')
                                        ->requiresConfirmation()
                                        ->modalHeading('Pengesahan')
                                        ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
                                        ->action(fn() => $this->save()),
                                ),

                            // Action::make('status')
                            //     ->label('Kemaskini Status')
                            //     ->icon('heroicon-o-pencil-square')
                            //     ->color('warning')
                            //     ->form([
                            //         Select::make('status')
                            //             ->label('Status')
                            //             ->required()
                            //             ->searchable()
                            //             ->options([
                            //                 'active' => 'Aktif',
                            //                 'pindaan nama' => 'Pindaan Nama',
                            //                 'batal nama' => 'Batal Nama',
                            //                 'removed' => 'Dibuang',
                            //             ])
                            //             ->default(fn($record) => $record->status),
                            //     ])
                            //     ->action(function (array $data, $record) {
                            //         $record->update([
                            //             'status' => $data['status'],
                            //         ]);
                            //     })
                            //     ->requiresConfirmation()
                            //     ->modalHeading('Kemaskini Status')
                            //     ->modalSubmitActionLabel('Simpan')
                            //     ->modalCancelActionLabel('Batal')

                            Action::make('status')
                                ->label('Buang Jawatan')
                                ->icon('heroicon-o-trash')
                                ->visible(fn($record) => $this->getOwnerRecord()->jenis === 'Tolak' && $record->status !== 'removed')
                                ->color('danger')
                                ->action(function ($record) {

                                    $waran = $this->getOwnerRecord();

                                    $record->update([
                                        'waran_tolak_id' => $waran->id,
                                        'status' => 'removed',
                                    ]);

                                    $record->delete();

                                    Notification::make()
                                        ->title('Berjaya dibuang')
                                        ->body('Jawatan telah berjaya dibuang.')
                                        ->success()
                                        ->send();
                                })
                                ->requiresConfirmation()
                                ->modalHeading('Buang Jawatan')
                                ->modalDescription('Adakah anda pasti mahu membuang rekod ini?')
                                ->modalSubmitActionLabel('Ya, Buang Jawatan')
                                ->modalCancelActionLabel('Batal'),

                            Action::make('restore')
                                ->label('Aktifkan Jawatan')
                                ->icon('heroicon-o-trash')
                                ->visible(fn($record) => $this->getOwnerRecord()->jenis === 'Tolak' && $record->status == 'removed')
                                ->color('success')
                                ->action(function ($record) {

                                    $waran = $this->getOwnerRecord();

                                    $record->update([
                                        'waran_tolak_id' => $waran->id,
                                        'status' => 'active',

                                    ]);

                                    $record->restore();

                                    Notification::make()
                                        ->title('Berjaya diaktifkan')
                                        ->body('Jawatan telah berjaya diaktifkan semula.')
                                        ->success()
                                        ->send();
                                })

                                ->requiresConfirmation()
                                ->modalHeading('Aktifkan Jawatan')
                                ->modalDescription('Adakah anda pasti mahu aktifkan semula rekod ini?')
                                ->modalSubmitActionLabel('Ya, Aktifkan Jawatan')
                                ->modalCancelActionLabel('Batal')
                                ->successRedirectUrl(null)
                        ]),

                    EditAction::make()
                        ->modalCancelActionLabel('Batal')
                        ->modalSubmitAction(
                            fn($action) => $action
                                ->label('Simpan2')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->modalHeading('Pengesahan')
                                ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
                                ->action(fn() => $this->save()),
                        ),
                    Action::make('delete')
                        ->label('Padam')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => "Padam")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->visible(
                            fn($record) =>
                            $this->getOwnerRecord()->jenis === 'Tambah'
                            && $record->status === 'active'
                        )
                        ->action(function ($record) {

                            $record->forcedelete();
                        })
                        ->after(function ($record) {
                            Log::info('Penempatan deleted', [
                                'waran_jawatan_id' => $record->id,
                                'user_id' => auth()->id(),
                            ]);

                            $creator = auth()->user();

                            $noWaran = $this->getOwnerRecord()->no_waran;

                            $recipients = User::whereIN('role', [1, 2])->get();

                            Notification::make()
                                ->title('Penempatan Deleted')
                                ->body("Penempatan for Waran {$noWaran} deleted by {$creator->name}")
                                ->danger()
                                ->sendToDatabase($recipients);

                        }),
                    Action::make('status')
                        ->label('Kemaskini Status')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->color('success')
                        ->visible(
                            fn() =>
                            $this->getOwnerRecord()->jenis === 'Tambah'
                        )
                        ->form([
                            Select::make('status')
                                ->label('Status')
                                ->required()
                                ->searchable()
                                ->options([
                                    'active' => 'Aktif',
                                    'pindaan nama' => 'Pindaan Nama',
                                    'batal nama' => 'Batal Nama',
                                ])
                                ->default(fn($record) => $record->status),
                        ])
                        ->action(function (array $data, $record) {
                            $record->update([
                                'status' => $data['status'],
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Kemaskini Status')
                        ->modalSubmitActionLabel('Simpan')
                        ->modalCancelActionLabel('Batal')
                ]),


            ]);


    }

}
