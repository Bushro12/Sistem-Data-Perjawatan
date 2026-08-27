<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\Aktiviti;
use App\Models\Bahagian;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\OpsyenPencen;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Subunit;
use App\Models\Unit;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Wizard::make([
                    Step::make('Maklumat Pegawai')
                        ->schema([
                            TextInput::make('nama')
                                ->label('Nama')
                                ->columnSpanFull()
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->dehydrateStateUsing(fn (string $state): string => strtoupper($state))
                                ->extraInputAttributes(['style' => 'text-transform:uppercase']),

                            TextInput::make('nokp')
                                ->label('No Kad Pengenalan')
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->reactive()
                                // ->unique(ignoreRecord: true, column: 'nokp')
                                ->afterStateUpdated(function ($state, callable $set) {

                                    if (! $state || strlen($state) < 6) {
                                        return;
                                    }

                                    // remove dash if user types it
                                    $noKp = str_replace('-', '', $state);

                                    $year = substr($noKp, 0, 2);
                                    $month = substr($noKp, 2, 2);
                                    $day = substr($noKp, 4, 2);

                                    // determine century
                                    $fullYear = $year > date('y') ? '19'.$year : '20'.$year;

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
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->options([
                                    'Lelaki' => 'Lelaki',
                                    'Perempuan' => 'Perempuan',
                                ]),
                            Select::make('jawatan_id')
                                ->label('Jawatan')
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
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

                                    if (! $jawatanGredId) {
                                        return;
                                    }

                                    $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                                    if (! $jawatanGred) {
                                        return;
                                    }

                                    $set('jawatan_id', $jawatanGred->jawatan_id);
                                }),

                            Select::make('gred_id')
                                ->label('Gred')
                                ->required()
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
                                ->disabled(fn (Get $get, ?Pegawai $record): bool => blank($get('jawatan_id')) || ($record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false))
                                ->afterStateHydrated(function ($state, Get $get, Set $set) {

                                    $jawatanGredId = $get('jawatan_gred_id');

                                    if (! $jawatanGredId) {
                                        return;
                                    }

                                    $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                                    if (! $jawatanGred) {
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

                                    // reset dependent fields
                                    $set('pegawai_id', null);
                                    $set('butiran', null);
                                }),
                            Hidden::make('jawatan_gred_id'),

                            Select::make('ptj_id')
                                ->label('PTJ')
                                ->relationship(
                                    'ptj',
                                    'nama_ptj',
                                    modifyQueryUsing: function (Builder $query) {
                                        $user = auth()->user();

                                        if ($user->role == 3) {
                                            $query->where('id', $user->ptj_id);
                                        }
                                    }
                                )
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->searchable()
                                ->preload()
                                ->columnSpanFull()
                                ->reactive()
                                ->visible(function (?Pegawai $record): bool {
                                    if (! $record) {
                                        // Create page
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                })
                                ->afterStateUpdated(fn ($state, callable $set) => $set('bahagian_id', null)),

                            TextEntry::make('ptj')
                                ->label('PTJ')
                                ->getStateUsing(function ($record) {
                                    return $record->ptj?->nama_ptj ?? '-';
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3 && auth()->user()->ptj_id !== $get('ptj_id'))
                                ->columnSpanFull(),

                            Select::make('bahagian_id')
                                ->label('Bahagian')
                                ->options(function (Get $get) {
                                    $ptjId = $get('ptj_id');

                                    if (! $ptjId) {
                                        return [];
                                    }

                                    return Bahagian::where('ptj_id', $ptjId)
                                        ->pluck('nama_bahagian', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->preload()
                                ->visible(function (?Pegawai $record): bool {
                                    if (! $record) {
                                        // Create page
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                })
                                ->columnSpanFull(),

                            TextEntry::make('bahagian')
                                ->label('Bahagian')
                                ->getStateUsing(function ($record) {
                                    return $record->bahagian?->nama_bahagian ?? '-';
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3 && auth()->user()->ptj_id !== $get('ptj_id'))
                                ->columnSpanFull(),

                            Grid::make(4)
                                ->schema([
                                    Select::make('unit_id')
                                        ->label('Unit')
                                        ->options(function (Get $get) {
                                            $bahagianId = $get('bahagian_id');

                                            if (! $bahagianId) {
                                                return [];
                                            }

                                            return Unit::where('bahagian_id', $bahagianId)
                                                ->pluck('nama_unit', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn (Get $get, ?Pegawai $record): bool => $get('ada_unit') || ($record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false))
                                        ->dehydrated(fn (Get $get) => ! $get('ada_unit'))
                                        ->nullable()
                                        ->columnSpan(4),

                                    Checkbox::make('ada_unit')
                                        ->label('Tiada Unit')
                                        ->live()
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                        ->columnSpan(1),

                                ])
                                ->visible(function (?Pegawai $record): bool {
                                    if (! $record) {
                                        // Create page
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                }),

                            TextEntry::make('unit')
                                ->label('Unit')
                                ->getStateUsing(function ($record) {
                                    return $record->unit?->namaUnit ?? '';
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3 && auth()->user()->ptj_id !== $get('ptj_id')),

                            Grid::make(4)
                                ->schema([
                                    Select::make('subunit_id')
                                        ->label('Subunit')
                                        ->options(function (Get $get) {
                                            $unitId = $get('unit_id');

                                            if (! $unitId) {
                                                return [];
                                            }

                                            return Subunit::where('unit_id', $unitId)
                                                ->pluck('nama_subunit', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn (Get $get, ?Pegawai $record): bool => $get('ada_subunit') || ($record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false))
                                        ->dehydrated(fn (Get $get) => ! $get('ada_subunit'))
                                        ->nullable()
                                        ->columnSpan(4),

                                    Checkbox::make('ada_subunit')
                                        ->label('Tiada Subunit')
                                        ->live()
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                        ->columnSpan(1),
                                ])
                                ->visible(function (?Pegawai $record): bool {
                                    if (! $record) {
                                        // Create page
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                }),

                            TextEntry::make('subunit')
                                ->label('Subunit')
                                ->getStateUsing(function ($record) {
                                    return $record->subunit?->namaSubunit ?? '';
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3 && auth()->user()->ptj_id !== $get('ptj_id')),

                        ]),

                    Step::make('Jenis Lantikan')
                        ->schema([
                            Checkbox::make('is_tetap')
                                ->label('TETAP')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kontrak', false);
                                        $set('is_kontrak_interim', false);
                                        $set('is_kontrak_isi_tetap', false);
                                    }
                                }),
                            Checkbox::make('is_kup')
                                ->label('KHAS UNTUK PENYANDANG (KUP)')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kupj', false);
                                        $set('is_jtw', false);
                                    }
                                }),
                            Checkbox::make('is_kontrak')
                                ->label('KONTRAK')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_tetap', false);
                                        $set('is_kontrak_interim', false);
                                        $set('is_kontrak_isi_tetap', false);
                                    }
                                }),

                            Checkbox::make('is_kupj')
                                ->label('KHAS UNTUK PENYANDANG JAWATAN (KUPJ)')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kup', false);
                                        $set('is_jtw', false);

                                    }
                                }),

                            Checkbox::make('is_kontrak_interim')
                                ->label('KONTRAK INTERIM')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kontrak', false);
                                        $set('is_tetap', false);
                                        $set('is_kontrak_isi_tetap', false);
                                    }
                                }),

                            Checkbox::make('is_jtw')
                                ->label('JAWATAN TANPA WARAN (JTW)')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kup', false);
                                        $set('is_kupj', false);

                                    }
                                }),

                            Checkbox::make('is_kontrak_isi_tetap')
                                ->label('KONTRAK ISI TETAP')
                                ->reactive()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $set('is_kontrak', false);
                                        $set('is_tetap', false);
                                        $set('is_kontrak_interim', false);
                                    }
                                }),

                            Section::make('Maklumat Lantikan')
                                ->columns(2)
                                ->columnSpanFull()
                                ->visible(fn (Get $get) => $get('is_tetap') || $get('is_kontrak_interim'))
                                ->schema([
                                    DatePicker::make('tarikh_lantikan')
                                        ->label('Tarikh Lantikan')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_sah_jawatan')
                                        ->label('Tarikh Sah Jawatan')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    Select::make('opsyen_pencen_id')
                                        ->label('Opsyen Pencen')
                                        ->relationship('opsyenPencen', 'opsyen')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
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
                                                ? '19'.$year
                                                : '20'.$year;

                                            try {

                                                $tarikhLahir = Carbon::createFromFormat(
                                                    'Y-m-d',
                                                    "$fullYear-$month-$day"
                                                );

                                                $opsyen = OpsyenPencen::find($state);

                                                if (! $opsyen) {
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
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                ]),

                            Section::make('Maklumat Lantikan Kontrak')
                                ->columns(2)
                                ->columnSpanFull()
                                ->visible(fn (Get $get) => $get('is_kontrak') || $get('is_kontrak_isi_tetap'))
                                ->schema([
                                    DatePicker::make('tarikh_lantikan1')
                                        ->label('Tarikh Lantikan 1')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->required()
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_tamat1')
                                        ->label('Tarikh Tamat 1')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->required()
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_lantikan2')
                                        ->label('Tarikh Lantikan 2')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_tamat2')
                                        ->label('Tarikh Tamat 2')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_lantikan3')
                                        ->label('Tarikh Lantikan 3')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_tamat3')
                                        ->label('Tarikh Tamat 3')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_lantikan4')
                                        ->label('Tarikh Lantikan 4')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_tamat4')
                                        ->label('Tarikh Tamat 4')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_lantikan5')
                                        ->label('Tarikh Lantikan 5')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),
                                    DatePicker::make('tarikh_tamat5')
                                        ->label('Tarikh Tamat 5')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false),

                                ]),
                        ]),

                    Step::make('Penempatan')
                        ->schema([
                            Grid::make(2)
                                ->columnSpanFull()
                                ->schema([
                                    DatePicker::make('tarikh_sandang')
                                        ->label('Tarikh Sandang')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->maxDate(Carbon::now()->endOfYear())
                                        ->live(),

                                    TextEntry::make('tahun_khidmat_penempatan_semasa')
                                        ->label('Tahun Khidmat Penempatan Semasa')
                                        ->getStateUsing(function ($record, Get $get) {
                                            $tarikhSandang = $get('tarikh_sandang') ?: $record?->tarikh_sandang;

                                            if (blank($tarikhSandang)) {
                                                return '-';
                                            }

                                            try {
                                                $sandang = Carbon::parse($tarikhSandang);
                                            } catch (\Exception $e) {
                                                return '-';
                                            }

                                            return Carbon::now()->year - $sandang->year;
                                        }),
                                ]),

                            TextEntry::make('no_waran')
                                ->label('No Waran')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    }

                                    return $record->waranJawatan?->waran?->no_waran;
                                })
                                ->visible(fn (Get $get): bool => ! $get('is_kontrak')),

                            TextEntry::make('butiran')
                                ->label('Butiran')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    }
                                    $waranJawatan = $record->waranJawatan;
                                    $butiran = $waranJawatan?->butiran;

                                    return $butiran;
                                })
                                ->visible(fn (Get $get): bool => ! $get('is_kontrak')),
                            TextEntry::make('ptj')
                                ->label('PTJ')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    } elseif (! $record->is_kontrak) {
                                        $waranJawatan = $record->waranJawatan;
                                        $ptj = $waranJawatan->ptj?->nama_ptj ?? '';
                                    } else {
                                        $ptj = $record->ptj?->nama_ptj;
                                    }

                                    return $ptj;
                                })
                                // ->visible(fn(Get $get): bool => !$get('is_kontrak'))
                                ->columnSpanFull(),

                            TextEntry::make('bahagian')
                                ->label('Bahagian')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    } elseif (! $record->is_kontrak) {
                                        $waranJawatan = $record->waranJawatan;
                                        $bahagian = $waranJawatan->bahagian?->nama_bahagian ?? '';
                                    } else {
                                        $bahagian = $record->bahagian?->nama_bahagian ?? '';
                                    }

                                    return $bahagian;
                                })
                                ->columnSpanFull(),

                            TextEntry::make('unit')
                                ->label('Unit')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    } elseif (! $record->is_kontrak) {
                                        $waranJawatan = $record->waranJawatan;
                                        $unit = $waranJawatan->unit?->namaUnit ?? '';
                                    } else {
                                        $unit = $record->unit?->nama_unit ?? '';
                                    }

                                    return $unit;
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3),

                            TextEntry::make('subunit')
                                ->label('Subunit')
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    } elseif (! $record->is_kontrak) {
                                        $waranJawatan = $record->waranJawatan;
                                        $subunit = $waranJawatan->subunit?->nama_subunit ?? '';
                                    } else {
                                        $subunit = $record->subunit?->nama_subunit;
                                    }

                                    return $subunit;
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3),
                            Group::make()
                                ->columns(2)
                                ->columnSpanFull()
                                ->relationship('pegawaiKontrak')
                                ->schema([
                                    Select::make('program_id')
                                        ->label('Program')
                                        ->options(
                                            Program::query()
                                                ->orderBy('nama_program')
                                                ->get()
                                                ->mapWithKeys(fn ($program) => [
                                                    $program->id => "{$program->nama_program} - {$program->desc_program}",
                                                ])
                                        )
                                        ->live()
                                        ->searchable()
                                        ->required(),

                                    Select::make('aktiviti_id')
                                        ->label('Aktiviti')
                                        ->options(function (Get $get) {
                                            $programId = $get('program_id');

                                            if (! $programId) {
                                                return [];
                                            }

                                            return Aktiviti::where('program_id', $programId)
                                                ->orderBy('no_aktivit')
                                                ->get()
                                                ->mapWithKeys(fn ($aktiviti) => [
                                                    $aktiviti->id => "{$aktiviti->no_aktivit} - {$aktiviti->nama_aktiviti}",
                                                ]);
                                        })
                                        ->searchable()
                                        ->required(),
                                ])
                                ->visible(fn (Get $get) => $get('is_kontrak')),
                            TextEntry::make('program')
                                ->label('program')
                                ->getStateUsing(function ($record) {
                                    // No record on the create page — nothing to show yet.
                                    if (! $record) {
                                        return null;
                                    }

                                    $program = $record->waranJawatan?->aktiviti?->program;

                                    return $program
                                        ? "{$program->nama_program} : {$program->desc_program}"
                                        : '-';
                                })
                                ->visible(fn (Get $get) => $get('is_kontrak_interim') || $get('is_tetap')),

                            TextEntry::make('aktiviti')
                                ->label('Aktiviti')
                                ->getStateUsing(function ($record) {
                                    // No record on the create page — nothing to show yet.
                                    if (! $record) {
                                        return null;
                                    }

                                    $aktiviti = $record->waranJawatan?->aktiviti;

                                    return $aktiviti
                                        ? "{$aktiviti->no_aktivit} - {$aktiviti->nama_aktiviti}"
                                        : '-';
                                })
                                ->visible(fn (Get $get) => $get('is_kontrak_interim') || $get('is_tetap')),

                            TextEntry::make('lain-lain')
                                ->label('Lain-lain')
                                ->getStateUsing(function ($record) {
                                    // No record on the create page — nothing to show yet.
                                    if (! $record) {
                                        return null;
                                    }

                                    $isKontrak = $record->is_kontrak == 1;
                                    $waranJawatan = $record->waranJawatan;

                                    // If pegawai doesn't have waran jawatan
                                    if (! $waranJawatan) {
                                        return 'Tiada';
                                    }

                                    $ptjPegawaiId = $record->ptj?->id;
                                    $ptjWaranId = $waranJawatan?->ptj?->id;

                                    return (! $isKontrak && $ptjPegawaiId !== $ptjWaranId)
                                        ? 'Pinjam'
                                        : 'Tiada';
                                })
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'Pinjam' => 'danger',
                                    'Tiada' => 'success',
                                    default => 'gray',
                                })
                                ->size('lg'),

                            DatePicker::make('tarikh_pinjam')
                                ->label('Tarikh Pinjam')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->visible(function ($record) {
                                    if (! $record) {
                                        return false;
                                    }

                                    $waranJawatan = $record->waranJawatan;

                                    if (! $waranJawatan) {
                                        return false;
                                    }

                                    return ! $record->is_kontrak
                                        && $record->ptj?->id !== $waranJawatan?->ptj?->id;
                                })
                                ->required(function ($record) {
                                    if (! $record) {
                                        return false;
                                    }

                                    return ! $record->is_kontrak
                                        && $record->ptj?->id !== $record->waranJawatan?->ptj?->id;
                                }),

                        ]),

                ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->nextAction(fn ($action) => $action->label('Seterusnya'))
                    ->previousAction(fn ($action) => $action->label('Kembali'))
                    ->submitAction(new HtmlString(
                        Blade::render(<<<'BLADE'
                            <div class="flex gap-2 justify-end">
                                <x-filament::button
                                    type="button"
                                    wire:click="validateBeforeSubmit"
                                    size="sm"
                                >
                                    Simpan
                                </x-filament::button>
                            </div>
                        BLADE)
                    )),

            ]);
    }
}
