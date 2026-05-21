<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\OpsyenPencen;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                        // DatePicker::make('tarikh_lahir')
                        //     ->label('Tarikh Lahir')
                        //     ->native(false)
                        //     ->displayFormat('d F Y')
                        //     ->dehydrated(false),
                        //     TextInput::make('emel')
                        //     ->label('E-mel')
                        //     ->email(),
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

                        Select::make('jawatan_gred_id')
                            ->label('Jawatan')
                            // ->multiple()
                            ->relationship(
                                'jawatan_gred',
                                'id',
                                fn($query) => $query
                                    ->join('jawatans', 'jawatan__greds.jawatan_id', '=', 'jawatans.id')
                                    ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                                    ->select('jawatan__greds.*')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->jawatan->desc_jawatan . ' (' . $record->gred->kod_gred . ')'
                            )
                            ->searchable([
                                'jawatans.desc_jawatan',
                                'greds.kod_gred'
                            ])
                            ->preload(),

                        Select::make('gred_id')
                            ->label('Gred')
                            ->searchable()
                            ->preload(),

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
                        Select::make('opsyen_pencen')
                            ->label('Opsyen Pencen')
                            ->relationship('opsyenPencen', 'opsyen')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {

                                $dob = $get('tarikh_lahir');

                                if (!$dob || !$state)
                                    return;

                                // 🔥 get actual value from DB
                                $opsyen = OpsyenPencen::find($state);

                                if (!$opsyen)
                                    return;

                                $umur = $opsyen->opsyen; // 56

                                $tarikhPencen = Carbon::parse($dob)->addYears((int) $umur);

                                $set('tarikh_pencen', $tarikhPencen->format('Y-m-d'));
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
