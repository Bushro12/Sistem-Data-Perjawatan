<?php

namespace App\Filament\Resources\WaranJawatans\Schemas;

use App\Models\Bahagian;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use App\Models\WaranJawatan;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class WaranJawatanForm
{
    public static function configure(Schema $schema): Schema
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

                            ]),


                        Tab::make('Nama Penyandang')
                            ->schema([
                                Select::make('pegawai_id')
                                    ->live()
                                    ->label('Pegawai')
                                    ->options(function (Get $get, $record) {

                                        $jawatanIds = $get('jawatan_ids');
                                        $gredIds = $get('gred_ids');

                                        if (blank($jawatanIds) || blank($gredIds)) {
                                            return [];
                                        }

                                        $jawatanGredIds = Jawatan_Gred::query()
                                            ->whereIn('jawatan_id', $jawatanIds)
                                            ->whereIn('gred_id', $gredIds)
                                            ->pluck('id');


                                        $query = Pegawai::query()
                                            ->whereIn('jawatan_gred_id', $jawatanGredIds)
                                            ->where('is_kontrak', false);


                                        // Admin & superadmin can see all PTJ
                                        if (!in_array(auth()->user()->role, [1, 2])) {

                                            // Normal user only sees own PTJ
                                            $query->where('ptj_id', auth()->user()->ptj_id);
                                        }


                                        $pegawai = $query
                                            ->orderBy('nama')
                                            ->get()
                                            ->mapWithKeys(function ($pegawai) {
                                                return [
                                                    $pegawai->id => "{$pegawai->nama} ({$pegawai->nokp})",
                                                ];
                                            })
                                            ->toArray();


                                        // Keep current selected pegawai visible even if different PTJ
                                        if ($record?->pegawai_id) {

                                            $currentPegawai = Pegawai::withoutGlobalScopes()
                                                ->find($record->pegawai_id);

                                            if ($currentPegawai) {
                                                $pegawai[$currentPegawai->id] =
                                                    "{$currentPegawai->nama} ({$currentPegawai->nokp})";
                                            }
                                        }
                                        return $pegawai;
                                    })
                                    ->disabled(function ($record, Get $get) {

                                        $user = auth()->user();

                                        // Admin & Superadmin can always edit
                                        if (in_array($user->role, [1, 2])) {
                                            return false;
                                        }

                                        // Role 3 cannot edit when is_kup is true
                                        if ($user->role == 3 && $get('is_kup')) {
                                            return true;
                                        }

                                        if (!$record?->pegawai_id) {
                                            return false;
                                        }

                                        $pegawai = Pegawai::withoutGlobalScopes()
                                            ->find($record->pegawai_id);

                                        return $pegawai?->ptj_id != $user->ptj_id;
                                    })
                                    ->dehydrated()

                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {

                                        if (blank($state) || blank($get('gred_ids'))) {
                                            $set('tbk', null);
                                            $set('tbk_gred_id', null);
                                            return;
                                        }

                                        $pegawai = Pegawai::withoutGlobalScopes()->with('jawatan_gred')->find($state);

                                        if (!$pegawai) {
                                            return;
                                        }

                                        $selectedGreds = \App\Models\Gred::query()->whereIn('id', $get('gred_ids'))->orderBy('kod_gred')->pluck('id')->values();
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
                                    ->columnSpanFull(),

                                Hidden::make('tbk'),
                                Hidden::make('tbk_gred_id'),

                                Textarea::make('catatan_jawatan')
                                    ->label('Catatan')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
