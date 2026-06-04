<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\OpsyenPencen;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Section::make('Maklumat Pegawai')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama')
                            ->columnSpanFull()
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                        TextInput::make('nokp')
                            ->label('No Kad Pengenalan')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {

                                if (!$state || strlen($state) < 6)
                                    return;

                                // remove dash if user types it
                                $noKp = str_replace('-', '', $state);

                                $year = substr($noKp, 0, 2);
                                $month = substr($noKp, 2, 2);
                                $day = substr($noKp, 4, 2);

                                // determine century
                                $fullYear = $year > date('y') ? '19' . $year : '20' . $year;

                                try {
                                    $dob = Carbon::createFromFormat('Y-m-d', "$fullYear-$month-$day");

                                    // set to tarikh_lahir field (UI only)
                                    $set('tarikh_lahir', $dob->format('Y-m-d'));
                                } catch (\Exception $e) {
                                    // invalid IC → ignore
                                }
                            }),

                        Select::make('jantina')
                            ->label('Jantina')
                            ->required()
                            ->options([
                                'lelaki' => 'Lelaki',
                                'perempuan' => 'Perempuan'
                            ]),
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->relationship('ptj', 'nama_ptj')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('bahagian_id', null)),

                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->options(function (Get $get) {
                                $ptjId = $get('ptj_id');

                                if (!$ptjId) {
                                    return [];
                                }

                                return \App\Models\Bahagian::where('ptj_id', $ptjId)
                                    ->pluck('nama_bahagian', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(function (Get $get) {
                                $bahagianId = $get('bahagian_id');

                                if (!$bahagianId) {
                                    return [];
                                }

                                return \App\Models\Unit::where('bahagian_id', $bahagianId)
                                    ->pluck('nama_unit', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        Select::make('subunit_id')
                            ->label('Subunit')
                            ->options(function (Get $get) {
                                $unitId = $get('unit_id');

                                if (!$unitId) {
                                    return [];
                                }

                                return \App\Models\Subunit::where('unit_id', $unitId)
                                    ->pluck('nama_subunit', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        Select::make('jawatan_id')
                            ->label('Jawatan')
                            ->options(
                                Jawatan::query()
                                    ->orderBy('desc_jawatan')
                                    ->pluck('desc_jawatan', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($state, Get $get, Set $set) {

                                $jawatanGredId = $get('jawatan_gred_id');

                                if (!$jawatanGredId) {
                                    return;
                                }

                                $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                                if (!$jawatanGred) {
                                    return;
                                }

                                $set('jawatan_id', $jawatanGred->jawatan_id);
                            }),

                        Select::make('gred_id')
                            ->label('Gred')
                            ->options(function (Get $get) {

                                $jawatanId = $get('jawatan_id');

                                if (blank($jawatanId)) {
                                    return [];
                                }

                                return Jawatan_Gred::query()
                                    ->where('jawatan_id', $jawatanId)
                                    ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                                    ->pluck('greds.kod_gred', 'greds.id')
                                    ->toArray();
                            })
                            ->live()
                            ->searchable()
                            ->preload()
                            ->dehydrated(false)
                            // ->multiple()
                            ->disabled(fn(Get $get) => blank($get('jawatan_id')))
                            ->afterStateHydrated(function ($state, Get $get, Set $set) {

                                $jawatanGredId = $get('jawatan_gred_id');

                                if (!$jawatanGredId) {
                                    return;
                                }

                                $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                                if (!$jawatanGred) {
                                    return;
                                }

                                $set('gred_id', $jawatanGred->gred_id);
                            })
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {

                                if (blank($state)) {
                                    return;
                                }

                                $jawatanGred = Jawatan_Gred::query()
                                    ->where('jawatan_id', $get('jawatan_id'))
                                    ->where('gred_id', $state)
                                    ->first();

                                $set('jawatan_gred_id', $jawatanGred?->id);

                                // 🔥 reset dependent fields
                                $set('pegawai_id', null);
                                $set('butiran', null);
                            }),
                        Hidden::make('jawatan_gred_id'),
                        Checkbox::make('is_kontrak')
                            ->label('KONTRAK')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $set('is_tetap', false);
                                    $set('is_kontrak_interim', false);
                                }
                            }),
                        Checkbox::make('is_kup')
                            ->label('KHAS UNTUK PENYANDANG (KUP)'),

                        Checkbox::make('is_tetap')
                            ->label('TETAP')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $set('is_kontrak', false);
                                    $set('is_kontrak_interim', false);
                                }
                            }),

                        Checkbox::make('is_kupj')
                            ->label('KUPJ'),

                        Checkbox::make('is_kontrak_interim')
                            ->label('KONTRAK INTERIM')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $set('is_kontrak', false);
                                    $set('is_tetap', false);
                                }
                            }),

                        Checkbox::make('is_jtw')
                            ->label('JAWATAN TANPA WARAN (JTW)'),

                    ]),
                Section::make('Maklumat Lantikan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn(Get $get) => !$get('is_kontrak'))
                    ->schema([
                        DatePicker::make('tarikh_lantikan')
                            ->label('Tarikh Lantikan')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_sah_jawatan')
                            ->label('Tarikh Sah Jawatan')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        Select::make('opsyen_pencen_id')
                            ->label('Opsyen Pencen')
                            ->relationship('opsyenPencen', 'opsyen')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                $nokp = $get('nokp');

                                if (blank($nokp) || blank($state)) {
                                    return;
                                }

                                // buang dash kalau ada
                                $nokp = str_replace('-', '', $nokp);

                                if (strlen($nokp) < 6) {
                                    return;
                                }

                                // extract DOB from IC
                                $year = substr($nokp, 0, 2);
                                $month = substr($nokp, 2, 2);
                                $day = substr($nokp, 4, 2);

                                // determine century
                                $fullYear = $year > date('y')
                                    ? '19' . $year
                                    : '20' . $year;

                                try {

                                    $tarikhLahir = Carbon::createFromFormat(
                                        'Y-m-d',
                                        "$fullYear-$month-$day"
                                    );

                                    $opsyen = OpsyenPencen::find($state);

                                    if (!$opsyen) {
                                        return;
                                    }

                                    $umurPersaraan = (int) $opsyen->opsyen;

                                    // tambah umur persaraan
                                    $tarikhPencen = $tarikhLahir
                                        ->copy()
                                        ->addYears($umurPersaraan);

                                    $set(
                                        'tarikh_pencen',
                                        $tarikhPencen->format('Y-m-d')
                                    );

                                } catch (\Exception $e) {
                                    return;
                                }
                            }),
                        DatePicker::make('tarikh_pencen')
                            ->label('Tarikh Pencen')
                            ->native(false)
                            ->displayFormat('d F Y'),
                    ]),

                Section::make('Maklumat Lantikan Kontrak')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn(Get $get) => $get('is_kontrak'))
                    ->schema([
                        DatePicker::make('tarikh_lantikan1')
                            ->label('Tarikh Lantikan 1')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_tamat1')
                            ->label('Tarikh Tamat 1')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_lantikan2')
                            ->label('Tarikh Lantikan 2')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_tamat2')
                            ->label('Tarikh Tamat 2')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_lantikan3')
                            ->label('Tarikh Lantikan 3')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_tamat3')
                            ->label('Tarikh Tamat 3')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_lantikan4')
                            ->label('Tarikh Lantikan 4')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_tamat4')
                            ->label('Tarikh Tamat 4')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_lantikan5')
                            ->label('Tarikh Lantikan 5')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        DatePicker::make('tarikh_tamat5')
                            ->label('Tarikh Tamat 5')
                            ->native(false)
                            ->displayFormat('d F Y'),

                    ])

            ]);
    }
}
