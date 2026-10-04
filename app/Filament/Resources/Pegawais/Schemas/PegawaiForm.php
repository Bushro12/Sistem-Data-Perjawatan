<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\Aktiviti;
use App\Models\Bahagian;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\OpsyenPencen;
use App\Models\Pegawai;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use App\Services\PrestasiService;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
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
                            TextInput::make('nokp')
                                ->label('No Kad Pengenalan')
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->dehydrated() // keep value on save even when disabled (waran-assigned)
                                ->maxLength(12)
                                ->placeholder('Contoh: 780102027168')
                                // ->helperText('Masukkan 12 digit No. KP - nama & jantina akan diisi automatik via API (jantina -> nama) jika ditemui.')
                                ->live(debounce: 500)
                                ->rules([
                                    fn (?Pegawai $record, $livewire): \Closure => function (string $attribute, $value, \Closure $fail) use ($record, $livewire): void {
                                        $normalized = str_replace('-', '', trim((string) $value));

                                        if (blank($normalized)) {
                                            return;
                                        }

                                        $ignoreId = $record?->getKey()
                                            ?? (method_exists($livewire, 'getRecord') ? $livewire->getRecord()?->getKey() : null);

                                        if (static::nokpAlreadyTaken($normalized, $ignoreId !== null ? (int) $ignoreId : null)) {
                                            $fail('Pegawai dengan No. Kad Pengenalan ini telah wujud dalam sistem.');
                                        }
                                    },
                                ])
                                ->afterStateUpdated(function (Get $get, Set $set, ?Pegawai $record, $livewire, ?string $state) {
                                    if (blank($state)) {
                                        return;
                                    }

                                    // remove dash if user types it
                                    $noKp = str_replace('-', '', trim((string) $state));

                                    // Immediate existence check for create (show message if pegawai already exists)
                                    if (preg_match('/^\d{12}$/', $noKp)) {
                                        $ignoreId = $record?->getKey()
                                            ?? (method_exists($livewire, 'getRecord') ? $livewire->getRecord()?->getKey() : null);

                                        if (static::nokpAlreadyTaken($noKp, $ignoreId !== null ? (int) $ignoreId : null)) {
                                            Notification::make()
                                                ->title('Pegawai Telah Wujud')
                                                ->body('Pegawai dengan No. Kad Pengenalan ini telah wujud dalam sistem.')
                                                ->danger()
                                                ->send();

                                            return;
                                        }
                                    }

                                    if (strlen($noKp) < 6) {
                                        return;
                                    }

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
                                        // invalid IC -> ignore
                                    }

                                    // Auto-fetch nama & jantina via API when 12-digit nokp entered (jantina -> nama)
                                    if (! preg_match('/^\d{12}$/', $noKp)) {
                                        return;
                                    }

                                    $result = PrestasiService::fetchByNokp($noKp);

                                    if ($result['found']) {
                                        if (filled($result['data']['nama'])) {
                                            $set('nama', strtoupper(ltrim((string) $result['data']['nama'], "'")));
                                        }

                                        if (filled($result['data']['jantinaNama'])) {
                                            $jantina = $result['data']['jantinaNama'];

                                            if (in_array($jantina, ['Lelaki', 'Perempuan'], true)) {
                                                $set('jantina', $jantina);
                                            }
                                        }

                                        // Auto-fill jawatan, gred, ptj, unit via API
                                        $ids = PrestasiService::resolvePegawaiFormIds($result['data']);

                                        if ($ids['ptj_id']) {
                                            $set('ptj_id', $ids['ptj_id']);
                                        }

                                        if ($ids['bahagian_id']) {
                                            $set('bahagian_id', $ids['bahagian_id']);
                                        } elseif ($ids['ptj_id'] && ! Ptj::usesBahagianHierarchyFor((int) $ids['ptj_id'])) {
                                            $set('bahagian_id', null);
                                        }

                                        if ($ids['unit_id']) {
                                            $set('unit_id', $ids['unit_id']);

                                            // Ensure "Tiada Unit" checkbox is unchecked when unit is found
                                            $set('ada_unit', false);
                                        }

                                        if ($ids['jawatan_id']) {
                                            $set('jawatan_id', $ids['jawatan_id']);
                                        }

                                        if ($ids['gred_id']) {
                                            $set('gred_id', $ids['gred_id']);
                                        }

                                        if ($ids['jawatan_gred_id']) {
                                            $set('jawatan_gred_id', $ids['jawatan_gred_id']);
                                        }

                                        // Auto-fill khidmat (Tetap/Kontrak/Kontrak Interim) via API khidmat.kod/nama
                                        $khidmatRaw = $result['data']['khidmatKod'] ?? $result['data']['khidmatNama'] ?? null;

                                        if (filled($khidmatRaw)) {
                                            $khidmat = strtoupper(trim((string) $khidmatRaw));
                                            $khidmat = preg_replace('/\s+/', ' ', $khidmat);

                                            if ($khidmat === 'TETAP') {
                                                $set('is_tetap', true);
                                                $set('is_kontrak', false);
                                                $set('is_kontrak_interim', false);
                                                $set('is_kontrak_isi_tetap', false);
                                            } elseif ($khidmat === 'KONTRAK') {
                                                $set('is_kontrak', true);
                                                $set('is_tetap', false);
                                                $set('is_kontrak_interim', false);
                                                $set('is_kontrak_isi_tetap', false);
                                            } elseif ($khidmat === 'KONTRAK INTERIM') {
                                                $set('is_kontrak_interim', true);
                                                $set('is_tetap', false);
                                                $set('is_kontrak', false);
                                                $set('is_kontrak_isi_tetap', false);
                                            }
                                        }

                                        // Conditional Tarikh & Opsyen based on khidmat
                                        $khidmatForDates = strtoupper(trim((string) ($result['data']['khidmatKod'] ?? $result['data']['khidmatNama'] ?? '')));
                                        $khidmatForDates = preg_replace('/\s+/', ' ', $khidmatForDates);
                                        $tarikhLantikan = $result['data']['tarikhLantikan'] ?? null;
                                        $tarikhSahJawatan = $result['data']['tarikhSahJawatan'] ?? null;
                                        $opsyenPencenId = $result['data']['opsyenPencenId'] ?? null;

                                        if ($khidmatForDates === 'KONTRAK') {
                                            if (filled($tarikhLantikan)) {
                                                $set('tarikh_lantikan1', $tarikhLantikan);
                                                $set('pegawaiKontrak.tarikh_lantikan1', $tarikhLantikan);
                                            }
                                        } elseif (in_array($khidmatForDates, ['TETAP', 'KONTRAK INTERIM'], true)) {
                                            if (filled($tarikhLantikan)) {
                                                $set('tarikh_lantikan', $tarikhLantikan);
                                            }

                                            if (filled($tarikhSahJawatan)) {
                                                $set('tarikh_sah_jawatan', $tarikhSahJawatan);
                                            }

                                            if (filled($opsyenPencenId)) {
                                                $set('opsyen_pencen_id', $opsyenPencenId);

                                                // Mirror existing opsyen_pencen_id afterStateUpdated: calculate tarikh_pencen from DOB + opsyen
                                                try {
                                                    if (strlen($noKp) >= 6) {
                                                        $year2 = substr($noKp, 0, 2);
                                                        $month2 = substr($noKp, 2, 2);
                                                        $day2 = substr($noKp, 4, 2);
                                                        $fullYear2 = $year2 > date('y') ? '19'.$year2 : '20'.$year2;
                                                        $tarikhLahir2 = Carbon::createFromFormat('Y-m-d', "$fullYear2-$month2-$day2");
                                                        $opsyen = OpsyenPencen::find($opsyenPencenId);

                                                        if ($opsyen) {
                                                            $tarikhPencen = $tarikhLahir2->copy()->addYears((int) $opsyen->opsyen);
                                                            $set('tarikh_pencen', $tarikhPencen->format('Y-m-d'));
                                                        }
                                                    }
                                                } catch (\Exception $e) {
                                                    // ignore
                                                }
                                            }
                                        }
                                    } elseif ($result['error']) {
                                        Notification::make()
                                            ->title('API Pegawai')
                                            ->body($result['error'])
                                            ->warning()
                                            ->send();
                                    }
                                }),
                            Select::make('jantina')
                                ->label('Jantina')
                                ->required()
                                // ->helperText('Akan diisi automatik via API: jantina -> nama (Lelaki/Perempuan).')
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->options([
                                    'Lelaki' => 'Lelaki',
                                    'Perempuan' => 'Perempuan',
                                ]),

                            TextInput::make('nama')
                                ->label('Nama')
                                ->columnSpanFull()
                                ->required()
                                // ->placeholder('Akan diisi automatik selepas No. KP dimasukkan (via API)')
                                // ->helperText('Nama akan diambil dari API luaran berdasarkan No. KP (jantina -> nama turut diisi). Boleh diedit manual jika perlu.')
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->dehydrateStateUsing(fn (string $state): string => strtoupper($state))
                                ->extraInputAttributes(['style' => 'text-transform:uppercase']),

                            Select::make('jawatan_id')
                                ->label('Jawatan')
                                ->required()
                                // ->helperText('Akan diisi automatik via API: jawatan -> nama')
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
                                // ->helperText('Akan diisi automatik via API: gred -> kod')
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
                                ->options(function (): array {
                                    $user = auth()->user();

                                    $query = Ptj::query()->orderBy('nama_ptj');

                                    if ($user->role == 3) {
                                        $query->where('id', $user->ptj_id);
                                    }

                                    return $query->pluck('nama_ptj', 'id')->toArray();
                                })
                                ->required()
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->searchable()
                                ->preload()
                                ->columnSpanFull()
                                ->live()
                                ->visible(function (?Pegawai $record): bool {
                                    if (! $record) {
                                        // Create page
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                })
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('bahagian_id', null);
                                    $set('unit_id', null);
                                    $set('subunit_id', null);
                                    $set('aktiviti_id', null);
                                    $set('program_id', null);
                                }),

                            TextEntry::make('ptj')
                                ->label('PTJ')
                                ->getStateUsing(function ($record) {
                                    return $record->ptj?->nama_ptj ?? '-';
                                })
                                ->visible(fn (Get $get) => auth()->user()->role == 3 && auth()->user()->ptj_id !== $get('ptj_id'))
                                ->columnSpanFull(),

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
                                ->required(fn (Get $get): bool => Ptj::usesBahagianHierarchyFor(
                                    filled($get('ptj_id')) ? (int) $get('ptj_id') : null
                                ))
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! Ptj::usesBahagianHierarchyFor(filled($get('ptj_id')) ? (int) $get('ptj_id') : null)) {
                                        return false;
                                    }

                                    if (! $record) {
                                        return true;
                                    }

                                    return auth()->user()->ptj_id === $record->ptj_id || auth()->user()->role == 1 || auth()->user()->role == 2;
                                })
                                ->disabled(fn (?Pegawai $record): bool => $record?->waranJawatan()->withoutGlobalScopes()->exists() ?? false)
                                ->dehydrated()
                                ->dehydrateStateUsing(function ($state, Get $get) {
                                    return Ptj::usesBahagianHierarchyFor(
                                        filled($get('ptj_id')) ? (int) $get('ptj_id') : null
                                    ) ? $state : null;
                                })
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('unit_id', null);
                                    $set('subunit_id', null);
                                })
                                ->columnSpanFull(),

                            Grid::make(4)
                                ->schema([
                                    Select::make('unit_id')
                                        ->label('Unit')
                                        ->options(function (Get $get) {
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
                                                    ->pluck('nama_unit', 'id');
                                            }

                                            return $query->where('ptj_id', $ptjId)
                                                ->pluck('nama_unit', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->disabled(fn (Get $get): bool => (bool) $get('ada_unit'))
                                        ->dehydrated(fn (Get $get) => ! $get('ada_unit'))
                                        ->nullable()
                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            if (filled($state)) {
                                                $set('ada_unit', false);
                                            }
                                            $set('subunit_id', null);
                                        })
                                        ->columnSpan(4),

                                    Checkbox::make('ada_unit')
                                        ->label('Tiada Unit')
                                        ->live()
                                        ->afterStateUpdated(function (bool $state, Set $set): void {
                                            if ($state) {
                                                $set('unit_id', null);
                                            }
                                        })
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
                                        ->disabled(fn (Get $get): bool => (bool) $get('ada_subunit'))
                                        ->dehydrated(fn (Get $get) => ! $get('ada_subunit'))
                                        ->nullable()
                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            if (filled($state)) {
                                                $set('ada_subunit', false);
                                            }
                                        })
                                        ->columnSpan(4),

                                    Checkbox::make('ada_subunit')
                                        ->label('Tiada Subunit')
                                        ->live()
                                        ->afterStateUpdated(function (bool $state, Set $set): void {
                                            if ($state) {
                                                $set('subunit_id', null);
                                            }
                                        })
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
                                ->visible(fn (Get $get): bool => (bool) ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak') || $get('is_kontrak_isi_tetap')))
                                ->schema([
                                    DatePicker::make('tarikh_sandang')
                                        ->label('Tarikh Sandang')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->maxDate(Carbon::now()->endOfYear())
                                        ->live(),

                                    TextEntry::make('tahun_khidmat_penempatan_semasa')
                                        ->label('Tahun Khidmat Penempatan Semasa')
                                        ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                        ->visible(fn (Get $get): bool => (bool) ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap')))
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

                                    TextEntry::make('tahun_perkhidmatan_semasa')
                                        ->label('Tahun Perkhidmatan Semasa')
                                        ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                        ->visible(fn (Get $get): bool => (bool) $get('is_kontrak'))
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
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    }

                                    return $record->waranJawatan?->waran?->no_waran;
                                })
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return ! $get('is_kontrak');
                                }),

                            TextEntry::make('butiran')
                                ->label('Butiran')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                ->getStateUsing(function ($record) {
                                    if (! $record) {
                                        return null;
                                    }
                                    $waranJawatan = $record->waranJawatan;
                                    $butiran = $waranJawatan?->butiran;

                                    return $butiran;
                                })
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return ! $get('is_kontrak');
                                }),
                            TextEntry::make('ptj')
                                ->label('PTJ')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                ->getStateUsing(function (?Pegawai $record, Get $get) {
                                    if (! $record) {
                                        // Create: when is_kontrak, use PTJ chosen in Maklumat Pegawai
                                        if ($get('is_kontrak')) {
                                            $ptjId = $get('ptj_id');

                                            if (blank($ptjId)) {
                                                return '-';
                                            }

                                            return Ptj::find($ptjId)?->nama_ptj ?? '-';
                                        }

                                        $ptjId = $get('ptj_id');

                                        if (filled($ptjId)) {
                                            return Ptj::find($ptjId)?->nama_ptj ?? '-';
                                        }

                                        return '-';
                                    }

                                    if (! $record->is_kontrak) {
                                        $waranJawatan = $record->waranJawatan;
                                        $ptj = $waranJawatan->ptj?->nama_ptj ?? '';
                                    } else {
                                        $ptj = $record->ptj?->nama_ptj;
                                    }

                                    return $ptj ?? '-';
                                })
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return true;
                                })
                                ->columnSpanFull(),

                            TextEntry::make('unit')
                                ->label('Unit')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
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
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return auth()->user()->role == 3;
                                }),

                            TextEntry::make('subunit')
                                ->label('Subunit')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
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
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return auth()->user()->role == 3;
                                }),
                            Hidden::make('program_id'),
                            Select::make('aktiviti_id')
                                ->label('Aktiviti')
                                ->options(fn (Get $get): array => static::kontrakAktivitiOptions($get('ptj_id')))
                                ->searchable()
                                ->required()
                                ->live()
                                ->visible(fn (Get $get) => $get('is_kontrak'))
                                ->disabled(fn (Get $get): bool => blank($get('ptj_id')))
                                ->helperText(fn (Get $get): ?string => blank($get('ptj_id'))
                                    ? 'Pilih PTJ dahulu untuk senarai aktiviti.'
                                    : null)
                                ->afterStateUpdated(function (Set $set, $state): void {
                                    $set('program_id', filled($state)
                                        ? Aktiviti::query()->whereKey($state)->value('program_id')
                                        : null);
                                })
                                ->columnSpanFull(),
                            TextEntry::make('aktiviti')
                                ->label('Aktiviti')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                ->getStateUsing(function ($record) {
                                    // No record on the create page - nothing to show yet.
                                    if (! $record) {
                                        return null;
                                    }

                                    $aktiviti = $record->waranJawatan?->aktiviti;

                                    return $aktiviti
                                        ? "{$aktiviti->no_aktivit} - {$aktiviti->nama_aktiviti}"
                                        : '-';
                                })
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return $get('is_kontrak_interim') || $get('is_tetap') || $get('is_kontrak_isi_tetap');
                                }),

                            TextEntry::make('lain-lain')
                                ->label('Lain-lain')
                                ->extraAttributes(['class' => 'pegawai-penempatan-normal-case'])
                                ->getStateUsing(function ($record) {
                                    // No record on the create page - nothing to show yet.
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
                                ->visible(function (Get $get, ?Pegawai $record): bool {
                                    if (! $record && ($get('is_tetap') || $get('is_kontrak_interim') || $get('is_kontrak_isi_tetap'))) {
                                        return false;
                                    }

                                    return true;
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

    /**
     * Whether another active pegawai already uses this NOKP (ignoring the
     * record being edited). Soft-deleted rows are ignored so re-created
     * kontrak pegawai are not blocked by old deleted duplicates.
     */
    public static function nokpAlreadyTaken(string $normalizedNokp, ?int $ignorePegawaiId = null): bool
    {
        return Pegawai::query()
            ->withoutGlobalScope('ptj_access')
            ->where(function ($query) use ($normalizedNokp): void {
                $query->where('nokp', $normalizedNokp)
                    ->orWhereRaw("REPLACE(nokp, '-', '') = ?", [$normalizedNokp]);
            })
            ->when($ignorePegawaiId, fn ($query) => $query->whereKeyNot($ignorePegawaiId))
            ->exists();
    }

    /**
     * Aktiviti for kontrak penempatan: only those under programs assigned to the PTJ,
     * and linked to that PTJ via aktiviti_ptj.
     *
     * @return array<int, string>
     */
    public static function kontrakAktivitiOptions(mixed $ptjId): array
    {
        if (blank($ptjId)) {
            return [];
        }

        return Ptj::query()->find((int) $ptjId)?->aktivitiSelectOptions() ?? [];
    }
}
