<?php

namespace App\Filament\Resources\LetakJawatans\Schemas;

use App\Models\LetakJawatan;
use App\Models\Pegawai;
use Date;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class LetakJawatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Pegawai')
                    ->schema([
                        Select::make('pegawai_id')
                        ->required()
                            ->label('Nama Pegawai')
                            ->visible(fn(string $operation) => $operation === 'create')
                            ->options(
                                Pegawai::orderBy('nama')->pluck('nama', 'id')
                            )
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

                                $set('tarikh_lantik', $tarikhLantik);

                                $set(
                                    'jawatan_display',
                                    $pegawai?->jawatan_gred?->jawatan?->desc_jawatan .
                                    ' (' . $pegawai?->jawatan_gred?->gred?->kod_gred . ')'
                                );

                                $set('lantikan', match (true) {
                                    $pegawai?->is_tetap == 1 => 'Tetap',
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
                            ->visible(fn(string $operation) => $operation === 'edit')
                            ->columnSpanFull()
                            ->readOnly()
                            ->formatStateUsing(
                                fn($get) =>
                                LetakJawatan::find($get('id'))?->nama
                            ),

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
                            ->formatStateUsing(
                                fn($get) =>
                                ($record = LetakJawatan::with(['jawatan_gred.jawatan', 'jawatan_gred.gred'])
                                    ->find($get('id')))
                                    ?->jawatan_gred?->jawatan?->desc_jawatan
                                . ' (' .
                                $record?->jawatan_gred?->gred?->kod_gred
                                . ')'
                            ),
                        Hidden::make('jawatan_gred_id')
                            ->dehydrated(),

                        TextInput::make('ptj_display')
                            ->label('Tempat Bertugas')
                            ->required()
                            ->readonly()
                            ->dehydrated(false)
                            ->columnSpanFull()
                             ->formatStateUsing(
                                fn($get) =>
                                LetakJawatan::find($get('id'))?->ptj?->nama_ptj
                            ),

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

                                if (!$tarikh)
                                    return;

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

                                if (!$state || !$jenis)
                                    return;

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

                        Checkbox::make('ikatan_lppsa')
                            ->label('Ikatan LPPSA (Perumahan)')
                            ->columnSpanFull(),

                        Textarea::make('alasan')
                            ->label('Alasan')
                            ->required()
                            ->columnSpanFull()

                    ])
                    ->columnSpanFull()
                    ->columns(2)

            ]);
    }
}
