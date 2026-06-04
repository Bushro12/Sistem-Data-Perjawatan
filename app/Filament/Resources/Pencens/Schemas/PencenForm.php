<?php

namespace App\Filament\Resources\Pencens\Schemas;

use App\Models\JenisPencen;
use App\Models\Pegawai;
use App\Models\Pencen;
use Blade;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PencenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Wizard::make([

                    Step::make('Maklumat Pegawai')
                        ->schema([
                            Select::make('nama_pegawai')
                                ->visible(fn(string $operation) => $operation === 'create')

                                ->label('Nama Pegawai')
                                ->options(
                                    Pegawai::orderBy('nama')->pluck('nama', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                // ->formatStateUsing(
                                //     fn($state) =>
                                //     Pencen::find($state)?->nama
                                // )
                                // ->columnSpanFull()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $pegawai = Pegawai::with([
                                        'jawatan_gred.jawatan',
                                        'jawatan_gred.gred',
                                        'ptj',
                                        'opsyenPencen'
                                        // 'bahagian',
                                        // 'unit',
                                        // 'subunit',
                                    ])->find($state);

                                    $ic = $pegawai?->nokp;

                                    $year = substr($ic, 0, 2);
                                    $month = substr($ic, 2, 2);
                                    $day = substr($ic, 4, 2);

                                    $fullYear = $year > date('y') ? "19$year" : "20$year";

                                    $birthday = Carbon::createFromDate($fullYear, $month, $day);

                                    $age = (int) $pegawai?->opsyenPencen?->opsyen;

                                    $retirementDate = $birthday->copy()->addYears($age);

                                    $set('nokp', $pegawai?->nokp);
                                    // $set('tarikh_lantikan', $pegawai?->tarikh_lantikan);
                                    $set('tarikh_sah_jawatan', $pegawai?->tarikh_sah_jawatan);
                                    $set('opsyen', $age);
                                    $set('tarikh_pencen', $retirementDate->format('d/m/Y'));
                                    $set('ptj', $pegawai?->ptj->nama_ptj);
                                    $set('jawatan', $pegawai?->jawatan_gred?->jawatan?->desc_jawatan);
                                    $set('gred', $pegawai?->jawatan_gred?->gred?->desc_gred);
                                    $lantikan = Carbon::parse($pegawai?->tarikh_lantikan);
                                    $bersara = $retirementDate;

                                    $diff = $lantikan->diff($bersara);

                                    $set(
                                        'tempoh_perkhidmatan',
                                        $diff->y . ' tahun, ' .
                                        $diff->m . ' bulan, ' .
                                        $diff->d . ' hari'
                                    );
                                    $kontrak = $pegawai?->pegawaiKontrak;

                                    $tarikhKontrak = $kontrak?->tarikh_lantikan5
                                        ?? $kontrak?->tarikh_lantikan4
                                        ?? $kontrak?->tarikh_lantikan3
                                        ?? $kontrak?->tarikh_lantikan2
                                        ?? $kontrak?->tarikh_lantikan1;

                                    $tarikhLantik = match (true) {

                                        $pegawai?->is_tetap == 1 => $pegawai?->tarikh_lantikan,

                                        $pegawai?->is_kontrak_interim == 1 => $pegawai?->tarikh_lantikan,

                                        $pegawai?->is_kontrak == 1 => $tarikhKontrak,

                                        default => null,
                                    };

                                    $jenisLantikan = match (true) {

                                        $pegawai?->is_tetap == 1 => 'Tetap',

                                        $pegawai?->is_kontrak_interim == 1 => 'Kontrak Interim',

                                        $pegawai?->is_kontrak == 1 => 'Kontrak',

                                        default => null,
                                    };

                                    $set('jenis_lantikan', $jenisLantikan);

                                    $set('tarikh_lantikan', $tarikhLantik);
                                    $set('ptj_id', $pegawai?->ptj_id);
                                    $set('jawatan_gred_id', $pegawai?->jawatan_gred_id);
                                    $set('nama', $pegawai?->nama);
                                    $set('opsyen_pencen_id', $pegawai?->opsyenPencen?->id);
                                }),

                            Hidden::make('nama')
                                ->dehydrated(),

                            TextInput::make('nama_pegawai1')
                                ->label('Nama Pegawai')
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn(string $operation) => $operation === 'edit')
                                ->formatStateUsing(
                                    fn($get) =>
                                    Pencen::find($get('id'))?->nama
                                )
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ]),

                            Hidden::make('jenis_lantikan')
                                ->dehydrated(),

                            TextInput::make('nokp')
                                ->label('No KP')
                                ->disabled()
                                ->dehydrated()
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ]),

                            TextInput::make('ptj')
                                ->label('PTJ')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpanFull()
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ])
                                ->formatStateUsing(
                                    fn($get) =>
                                    Pencen::with('ptj')
                                        ->find($get('id'))
                                        ?->ptj?->nama_ptj
                                ),

                            Hidden::make('ptj_id')
                                ->dehydrated(),

                            TextInput::make('jawatan')
                                ->label('Jawatan')
                                ->disabled()
                                ->dehydrated()
                                // ->columnSpanFull()
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ])
                                ->formatStateUsing(
                                    fn($get) =>
                                    Pencen::with('jawatan_gred.jawatan')
                                        ->find($get('id'))
                                        ?->jawatan_gred?->jawatan?->desc_jawatan
                                ),

                            TextInput::make('gred')
                                ->label('Gred')
                                ->disabled()
                                ->dehydrated()
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ])
                                ->formatStateUsing(
                                    fn($get) =>
                                    Pencen::with('jawatan_gred.jawatan')
                                        ->find($get('id'))
                                        ?->jawatan_gred?->gred?->kod_gred
                                ),

                            Hidden::make('jawatan_gred_id')
                                ->dehydrated(),

                        ]),
                    Step::make('Maklumat Persaran')

                        ->schema([


                            DatePicker::make('tarikh_lantikan')
                                ->label('Tarikh Lantikan')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->readonly(),

                            DatePicker::make('tarikh_sah_jawatan')
                                ->label('Tarikh Sah Jawatan')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->readonly(),

                            // TextInput::make('opsyen')
                            //     ->label('Opsyen (Umur Bersara)')
                            //     ->disabled()
                            //     ->dehydrated()
                            //     ->extraInputAttributes([
                            //         'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                            //         'style' => 'color: black; -webkit-text-fill-color: black;',
                            //     ]),

                            Select::make('jenis_pencen_id')
                            ->label('Jenis Penamatan Perkhidmatan')
                                ->options(
                                    JenisPencen::orderBy('jenis')->pluck('jenis', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->columnSpanFull(),

                            TextInput::make('opsyen')
                                ->label('Opsyen (Umur Bersara)')
                                ->disabled()
                                ->dehydrated()
                                ->visible(
                                    fn(callable $get) =>
                                    optional(JenisPencen::find($get('jenis_pencen_id')))->kategori === 'Paksa'
                                )
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ])
                                ->formatStateUsing(
                                    fn($get) =>
                                    Pencen::with('opsyenPencen')
                                        ->find($get('id'))
                                        ?->opsyenPencen?->opsyen
                                ),

                            Hidden::make('opsyen_pencen_id')
                                ->dehydrated(),

                            DatePicker::make('tarikh_pencen')
                                ->label('Tarikh Bersara')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->visible(
                                    fn(callable $get) =>
                                    optional(JenisPencen::find($get('jenis_pencen_id')))->kategori === 'Paksa'
                                )
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                                    $lantikan = Carbon::parse($get('tarikh_lantikan'));
                                    $pencen = Carbon::parse($state);

                                    $diff = $lantikan->diff($pencen);

                                    $set(
                                        'tempoh_perkhidmatan',
                                        $diff->y . ' tahun, ' .
                                        $diff->m . ' bulan, ' .
                                        $diff->d . ' hari'
                                    );
                                }),

                            TextInput::make('tempoh_perkhidmatan')
                                ->label('Tempoh Perkhidmatan')
                                ->visible(
                                    fn(callable $get) =>
                                    optional(JenisPencen::find($get('jenis_pencen_id')))->kategori === 'Paksa'
                                ),

                            DatePicker::make('tarikh_kuatkuasa')
                                ->label('Tarikh Kuatkuasa')
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->visible(
                                    fn(callable $get) =>
                                    optional(JenisPencen::find($get('jenis_pencen_id')))->kategori === 'Pilihan'
                                )
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                                    $lantikan = Carbon::parse($get('tarikh_lantikan'));
                                    $kuatkuasa = Carbon::parse($state);

                                    $diff = $lantikan->diff($kuatkuasa);

                                    $set(
                                        'tempoh_perkhidmatan',
                                        $diff->y . ' tahun, ' .
                                        $diff->m . ' bulan, ' .
                                        $diff->d . ' hari'
                                    );

                                    $set(
                                        'tempoh_perkhidmatan2',
                                        $diff->y . ' tahun, ' .
                                        $diff->m . ' bulan, ' .
                                        $diff->d . ' hari'
                                    );
                                }),

                            TextInput::make('tempoh_perkhidmatan2')
                                ->label('Tempoh Perkhidmatan')
                                ->disabled()
                                ->dehydrated()
                                ->visible(
                                    fn(callable $get) =>
                                    optional(JenisPencen::find($get('jenis_pencen_id')))->kategori === 'Pilihan'
                                )
                                ->extraInputAttributes([
                                    'class' => 'bg-white !bg-white text-black !text-black opacity-100 !opacity-100',
                                    'style' => 'color: black; -webkit-text-fill-color: black;',
                                ]),

                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->columnSpanFull()

                        ])
                ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->submitAction(new HtmlString(\Illuminate\Support\Facades\Blade::render(<<<'BLADE'
                        <div class="flex gap-2 justify-end">
                            <x-filament::button
                                type="submit"
                                size="sm"
                            >
                                Submit
                            </x-filament::button>
                        </div>
                    BLADE)))
            ]);
    }
}
