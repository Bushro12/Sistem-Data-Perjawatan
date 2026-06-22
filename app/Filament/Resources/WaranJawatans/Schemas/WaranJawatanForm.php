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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
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

                                        $pegawaiQuery = Pegawai::query()
                                            ->whereIn('jawatan_gred_id', $jawatanGredIds);

                                        // exclude already used pegawai, BUT allow current record value
                                        $usedPegawai = WaranJawatan::query()
                                            ->whereNotNull('pegawai_id')
                                            ->when(
                                                $record,
                                                fn($q) =>
                                                $q->where('id', '!=', $record->id)
                                            )
                                            ->pluck('pegawai_id');

                                        $pegawaiQuery->whereNotIn('id', $usedPegawai);

                                        return $pegawaiQuery->orderBy('nama')
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->columnSpanFull()
                                    ->searchable(),

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
}
