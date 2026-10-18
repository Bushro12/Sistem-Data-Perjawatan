<?php

namespace App\Filament\Resources\WaranJawatans\Schemas;

use App\Models\Aktiviti;
use App\Models\Bahagian;
use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class WaranJawatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Nama Penyandang')
                            ->schema([
                                Select::make('pegawai_id')
                                    ->live()
                                    ->label('Pegawai')
                                    ->options(fn (Get $get, $record): array => Pegawai::penyandangOptions(
                                        $get('jawatan_ids') ?? [],
                                        $get('gred_ids') ?? [],
                                        $record?->id,
                                        $record?->pegawai_id,
                                    ))
                                    ->disabled(function ($record, Get $get) {

                                        $user = auth()->user();

                                        // Admin & Superadmin can always edit
                                        if (in_array($user->role, [1, 2])) {
                                            return false;
                                        }

                                        // Role 3 cannot edit when is_kup is true
                                        if ($user->role == 3 && ($record?->is_kup || $get('is_kup'))) {
                                            return true;
                                        }

                                        if (! $record?->pegawai_id) {
                                            return false;
                                        }

                                        $pegawai = Pegawai::withoutGlobalScopes()
                                            ->find($record->pegawai_id);

                                        return $pegawai?->ptj_id != $user->ptj_id;
                                    })
                                    ->dehydrated(fn ($record, Get $get): bool => ! (
                                        auth()->user()?->role == 3
                                        && ($record?->is_kup || $get('is_kup'))
                                    ))

                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {

                                        if (blank($state) || blank($get('gred_ids'))) {
                                            $set('tbk', null);
                                            $set('tbk_gred_id', null);

                                            return;
                                        }

                                        $pegawai = Pegawai::withoutGlobalScopes()->with('jawatan_gred')->find($state);

                                        if (! $pegawai) {
                                            return;
                                        }

                                        $selectedGreds = Gred::query()->whereIn('id', $get('gred_ids'))->orderBy('kod_gred')->pluck('id')->values();
                                        $lowestGredId = $selectedGreds->first();
                                        $tbk = $selectedGreds->search($pegawai->jawatan_gred->gred_id);

                                        if ($tbk === false) {
                                            $set('tbk', null);
                                            $set('tbk_gred_id', null);

                                            return;
                                        }

                                        $set('tbk', $tbk);
                                        $set('tbk_gred_id', $lowestGredId);

                                    })->columnSpanFull()->searchable(),

                                Checkbox::make('is_kup')
                                    ->label('Khas Untuk Penyandang (KUP)')
                                    ->disabled(fn () => auth()->user()?->role == 3)
                                    ->dehydrated(fn () => auth()->user()?->role != 3)
                                    ->columnSpanFull(),

                                Hidden::make('tbk'),
                                Hidden::make('tbk_gred_id'),

                                Textarea::make('catatan_jawatan')
                                    ->label('Catatan')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Maklumat Jawatan')
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
                                                                $aktiviti->id => $aktiviti->no_aktivit.' - '.$aktiviti->nama_aktiviti,
                                                            ];
                                                        })
                                                        ->toArray(),
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('ptj_id', null);
                                        $set('bahagian_id', null);
                                        $set('unit_id', null);
                                        $set('subunit_id', null);
                                    })
                                    ->columns(1)
                                    ->disabled(
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
                                    ),
                                TextInput::make('butiran')
                                    ->required()
                                    ->maxLength(255)
                                    ->readonly(
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
                                    ),
                                DatePicker::make('tarikh_kuatkuasa')
                                    ->label('Tarikh Kuatkuasa Waran')
                                    ->native(false)
                                    ->displayFormat('d F Y')
                                    ->disabled(
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
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
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
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
                                    ->disabled(fn (Get $get) => blank($get('jawatan_ids')))
                                    ->searchable()
                                    ->preload()
                                    ->multiple()
                                    ->live(),

                                Select::make('ptj_id')
                                    ->label('PTJ')
                                    ->options(fn (Get $get): array => Aktiviti::ptjSelectOptionsFor(
                                        $get('aktiviti_id'),
                                        $get('ptj_id'),
                                    ))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText(fn (Get $get): ?string => blank($get('aktiviti_id'))
                                        ? 'Sila pilih aktiviti dahulu'
                                        : null)
                                    ->disabled(
                                        fn (Get $get): bool => blank($get('aktiviti_id'))
                                            || (
                                                ! auth()->user()?->isSuperadmin()
                                                && ! auth()->user()?->isAdmin()
                                            )
                                    )
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('bahagian_id', null);
                                        $set('unit_id', null);
                                        $set('subunit_id', null);
                                    }),

                                Select::make('bahagian_id')
                                    ->label('Bahagian')
                                    ->options(function (Get $get): array {
                                        $ptjId = $get('ptj_id');

                                        if (! $ptjId || ! Ptj::usesBahagianHierarchyFor((int) $ptjId)) {
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
                                    // ->required(fn (Get $get): bool => Ptj::usesBahagianHierarchyFor(
                                    //     filled($get('ptj_id')) ? (int) $get('ptj_id') : null
                                    // ))
                                    ->visible(fn (Get $get): bool => Ptj::usesBahagianHierarchyFor(
                                        filled($get('ptj_id')) ? (int) $get('ptj_id') : null
                                    ))
                                    ->dehydrated()
                                    ->dehydrateStateUsing(function ($state, Get $get) {
                                        return Ptj::usesBahagianHierarchyFor(
                                            filled($get('ptj_id')) ? (int) $get('ptj_id') : null
                                        ) ? $state : null;
                                    })
                                    ->columnSpanFull()
                                    ->disabled(
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
                                    )
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('unit_id', null);
                                        $set('subunit_id', null);
                                    }),

                                Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(function (Get $get): array {
                                        $ptjId = $get('ptj_id');

                                        if (! $ptjId) {
                                            return [];
                                        }

                                        $query = Unit::query()->orderBy('nama_unit');

                                        if (Ptj::usesBahagianHierarchyFor((int) $ptjId)) {
                                            $bahagianId = $get('bahagian_id');

                                            if (! $bahagianId) {
                                                return [];
                                            }

                                            return $query->where('bahagian_id', $bahagianId)
                                                ->pluck('nama_unit', 'id')
                                                ->toArray();
                                        }

                                        return $query->where('ptj_id', $ptjId)
                                            ->pluck('nama_unit', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->nullable()
                                    ->columnSpanFull()
                                    ->disabled(
                                        fn () => ! auth()->user()?->isSuperadmin()
                                        && ! auth()->user()?->isAdmin()
                                    )
                                    ->afterStateUpdated(fn (Set $set) => $set('subunit_id', null)),

                                Select::make('subunit_id')
                                    ->label('Subunit')
                                    ->options(function (Get $get): array {
                                        $unitId = $get('unit_id');

                                        if (! $unitId) {
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
                                    ->nullable()
                                    ->columnSpanFull()
                                    ->disabled(function (Get $get): bool {
                                        if (blank($get('unit_id'))) {
                                            return true;
                                        }

                                        return ! auth()->user()?->isSuperadmin()
                                            && ! auth()->user()?->isAdmin();
                                    }),

                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
