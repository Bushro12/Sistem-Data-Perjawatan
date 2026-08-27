<?php

namespace App\Filament\Resources\LetakJawatans\Schemas;

use App\Models\LetakJawatan;
use App\Models\Pegawai;
use App\Models\Pencen;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class LetakJawatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Wizard::make([
                    Step::make('Maklumat Pegawai')
                        ->schema([
                            Select::make('pegawai_id')
                                ->required()
                                ->label('Nama Pegawai')
                                ->visible(fn (string $operation) => $operation === 'create')
                                ->options(function (): array {
                                    return Pegawai::query()
                                        ->whereNotIn('nokp', LetakJawatan::withoutGlobalScopes()->whereNotNull('nokp')->select('nokp'))
                                        ->whereNotIn('nokp', Pencen::withoutGlobalScopes()->whereNotNull('nokp')->select('nokp'))
                                        ->orderBy('nama')
                                        ->pluck('nama', 'id')
                                        ->all();
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {

                                    $pegawai = Pegawai::with([
                                        'jawatan_gred.jawatan',
                                        'jawatan_gred.gred',
                                        'ptj',
                                        'bahagian',
                                        'unit',
                                        'subunit',
                                    ])->find($state);

                                    $set('nokp', $pegawai?->nokp);
                                    $kontrak = $pegawai?->pegawaiKontrak;

                                    // Latest contract renewal wins: the highest-numbered
                                    // non-null tarikh lantikan (5 → 1).
                                    $tarikhKontrak = $kontrak?->tarikh_lantikan5
                                        ?? $kontrak?->tarikh_lantikan4
                                        ?? $kontrak?->tarikh_lantikan3
                                        ?? $kontrak?->tarikh_lantikan2
                                        ?? $kontrak?->tarikh_lantikan1;

                                    $tarikhLantik = match (true) {

                                        $pegawai?->is_tetap == 1 => $pegawai?->tarikh_lantikan,

                                        $pegawai?->is_kontrak_interim == 1 => $pegawai?->tarikh_lantikan,

                                        $pegawai?->is_kontrak_isi_tetap == 1 => $tarikhKontrak,

                                        $pegawai?->is_kontrak == 1 => $tarikhKontrak,

                                        default => null,
                                    };

                                    $set('tarikh_lantik', $tarikhLantik);

                                    $set(
                                        'jawatan_display',
                                        $pegawai?->jawatan_gred?->jawatan?->desc_jawatan.
                                        ' ('.$pegawai?->jawatan_gred?->gred?->kod_gred.')'
                                    );

                                    $set('lantikan', match (true) {
                                        $pegawai?->is_tetap == 1 => 'Tetap',
                                        $pegawai?->is_kontrak_isi_tetap == 1 => 'Kontrak Isi Tetap',
                                        $pegawai?->is_kontrak == 1 => 'Kontrak',
                                        $pegawai?->is_kontrak_interim == 1 => 'Kontrak Interim',
                                        default => '-',
                                    });

                                    $pegawai = Pegawai::with('ptj')->find($state);

                                    // SAVE ID
                                    $set('ptj_id', $pegawai?->ptj_id);

                                    // DISPLAY NAME
                                    $set('ptj_display', $pegawai?->ptj?->nama_ptj);

                                    $pegawai = Pegawai::find($state);

                                    $set('jawatan_gred_id', $pegawai?->jawatan_gred_id);
                                    $set('nama', $pegawai?->nama);

                                    // ✅ ADD THIS (tempat bertugas hierarchy)
                                    // $set('tempat_bertugas',
                                    //     $pegawai?->subunit?->nama_subunit
                                    //     ?? $pegawai?->unit?->nama_unit
                                    //     ?? $pegawai?->bahagian?->nama_bahagian
                                    //     ?? $pegawai?->ptj?->nama_ptj
                                    //     ?? '-'
                                    // );
                                })
                                ->columnSpanFull(),

                            Hidden::make('nama')
                                ->dehydrated(),

                            TextInput::make('nama_Pegawai')
                                ->label('Nama Pegawai')
                                ->visible(fn (string $operation) => $operation === 'edit')
                                ->columnSpanFull()
                                ->readOnly()
                                ->afterStateHydrated(function (TextInput $component, ?LetakJawatan $record) {
                                    if (! $record?->exists) {
                                        return;
                                    }

                                    $component->state($record->nama);
                                }),

                            TextInput::make('nokp')
                                ->label('No KP')
                                ->required()
                                ->readonly()
                                ->dehydrated(),

                            TextInput::make('jawatan_display')
                                ->label('Jawatan / Gred')
                                ->required()
                                ->readonly()
                                ->dehydrated()
                                ->afterStateHydrated(function (TextInput $component, ?LetakJawatan $record) {
                                    if (! $record?->exists) {
                                        return;
                                    }

                                    $jawatan = $record->jawatan_gred?->jawatan?->desc_jawatan;
                                    $gred = $record->jawatan_gred?->gred?->kod_gred;

                                    $component->state(
                                        ! $jawatan && ! $gred
                                            ? ' '
                                            : $jawatan.($gred ? ' ('.$gred.')' : '')
                                    );
                                }),
                            Hidden::make('jawatan_gred_id')
                                ->dehydrated(),

                            TextInput::make('ptj_display')
                                ->label('Tempat Bertugas')
                                ->required()
                                ->readonly()
                                ->dehydrated(false)
                                ->columnSpanFull()
                                ->afterStateHydrated(function (TextInput $component, ?LetakJawatan $record) {
                                    if (! $record?->exists) {
                                        return;
                                    }

                                    $component->state($record->ptj?->nama_ptj ?? '-');
                                }),

                            Hidden::make('ptj_id')
                                ->dehydrated(),
                            TextInput::make('lantikan')
                                ->label('Lantikan')
                                ->required()
                                ->readonly()
                                ->dehydrated(),

                            DatePicker::make('tarikh_lantik')
                                ->label('Tarikh Lantikan')
                                ->required()
                                ->readonly()
                                ->dehydrated()
                                ->native(false)
                                ->displayFormat('d F Y'),
                        ]),
                    Step::make('Maklumat Peletakkan Jawatan')
                        ->schema([
                            Select::make('jenis_notis')
                                ->label('Notis (30 Hari @ 24 Jam)')
                                ->required()
                                ->options([
                                    '30 Hari' => '30 Hari',
                                    '24 Jam' => '24 Jam',
                                ])
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                    $tarikh = $get('tarikh_notis');

                                    if (! $tarikh) {
                                        return;
                                    }

                                    $set(
                                        'tarikh_kuatkuasa',
                                        match ($state) {
                                            '30 Hari' => Carbon::parse($tarikh)->addDays(30),
                                            '24 Jam' => Carbon::parse($tarikh)->addDay(),
                                            default => null,
                                        }
                                    );
                                }),

                            DatePicker::make('tarikh_notis')
                                ->label('Tarikh Mula Notis')
                                ->required()
                                ->live()
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                    $jenis = $get('jenis_notis');

                                    if (! $state || ! $jenis) {
                                        return;
                                    }

                                    $set(
                                        'tarikh_kuatkuasa',
                                        match ($jenis) {
                                            '30 Hari' => Carbon::parse($state)->addDays(30),
                                            '24 Jam' => Carbon::parse($state)->addDay(),
                                            default => null,
                                        }
                                    );
                                }),
                            DatePicker::make('tarikh_kuatkuasa')
                                ->label('Tarikh Kuatkuasa')
                                ->required()
                                ->readonly()
                                ->dehydrated()
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->columnSpanFull(),

                            Checkbox::make('ikatan_jpa')
                                ->label('Ikatan JPA')
                                ->columnSpanFull(),

                            Checkbox::make('ikatan_bpl')
                                ->label('Ikatan BPL')
                                ->columnSpanFull(),

                            Checkbox::make('pinjaman_lppsa')
                                ->label('Ikatan LPPSA (Perumahan)')
                                ->columnSpanFull(),

                            Textarea::make('alasan')
                                ->label('Alasan')
                                ->required()
                                ->columnSpanFull(),
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
