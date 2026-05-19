<?php

namespace App\Filament\Resources\Warans\Schemas;

use App\Filament\Resources\Warans\WaranResource;
use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\Waran;
use App\Models\WaranJawatan;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;

class WaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Section::make('Maklumat Waran')
                    ->schema([
                        TextInput::make('no_waran')
                            ->label('No Waran')
                            ->unique(Waran::class, 'no_waran', ignorable: fn(Get $get) => $get('id'))
                            ->reactive(),

                        TextInput::make('jik')
                            ->label('Jumlah Jawatan')
                            ->required()
                            ->numeric(),

                        Textarea::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        Radio::make('jenis')
                        ->label('Jenis Waran')
                            ->options([
                                'tambah' => 'Tambah Jawatan',
                                'tolak' => 'Tolak Jawatan',
                            ])
                            ->descriptions([
                                'tambah' => 'Menambah jawatan baru.',
                                'tolak' => 'Mengurangkan jawatan sedia ada.'
                            ])

                        // Actions::make([
                        //     Action::make('waranJawatan')
                        //         ->label('Tambah Jawatan')
                        //         ->modalHeading('Tambah Jawatan')
                        //         ->visible(fn($record) => filled($record?->id))

                        //         ->form([

                        //             Select::make('aktiviti_id')
                        //                 // ->relationship('aktiviti', 'nama_aktiviti')
                        //                 ->required()
                        //                 ->options(function () {

                        //                     return Program::with('aktiviti')
                        //                         ->orderBy('nama_program')
                        //                         ->get()
                        //                         ->mapWithKeys(function ($program) {

                        //                             return [
                        //                                 $program->nama_program => $program->aktiviti
                        //                                     ->mapWithKeys(function ($aktiviti) {
                        //                                         return [
                        //                                             $aktiviti->id => $aktiviti->no_aktivit . ' - ' . $aktiviti->nama_aktiviti
                        //                                         ];
                        //                                     })
                        //                                     ->toArray(),
                        //                             ];

                        //                         })
                        //                         ->toArray();
                        //                 })
                        //                 ->searchable()
                        //                 ->preload()
                        //                 ->columns(1),

                        //             TextInput::make('Butiran')
                        //                 ->label('Butiran')
                        //                 ->required(),

                        //             Select::make('jawatan_id')
                        //                 ->label('Jawatan')
                        //                 ->options(
                        //                     Jawatan::orderBy('desc_jawatan')
                        //                         ->pluck('desc_jawatan', 'id')
                        //                 )
                        //                 ->searchable()
                        //                 ->preload()
                        //                 ->live(),

                        //             Select::make('gred_ids')
                        //                 ->label('Gred')
                        //                 ->multiple()
                        //                 ->options(function (Get $get) {

                        //                     $jawatanId = $get('jawatan_id');

                        //                     if (!$jawatanId) {
                        //                         return [];
                        //                     }

                        //                     return Jawatan_Gred::query()
                        //                         ->where('jawatan_id', $jawatanId)
                        //                         ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                        //                         ->pluck('greds.kod_gred', 'greds.id')
                        //                         ->toArray();
                        //                 })
                        //                 ->disabled(fn(Get $get) => blank($get('jawatan_id')))
                        //                 ->searchable()
                        //                 ->preload()
                        //                 ->multiple()
                        //                 ->live(),

                        // Select::make('jawatan_id')
                        //     ->label('Jawatan')
                        //     ->options(
                        //         Jawatan::query()
                        //             ->orderBy('desc_jawatan')
                        //             ->pluck('desc_jawatan', 'id')
                        //     )
                        //     ->searchable()
                        //     ->preload()
                        //     ->live()
                        //     ->reactive()
                        //     ->dehydrated(false)
                        //     ->afterStateHydrated(function ($state, Get $get, Set $set) {

                        //         $jawatanGredId = $get('jawatan_gred_id');

                        //         if (!$jawatanGredId) {
                        //             return;
                        //         }

                        //         $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                        //         if (!$jawatanGred) {
                        //             return;
                        //         }

                        //         $set('jawatan_id', $jawatanGred->jawatan_id);
                        //     }),

                        // Select::make('gred_ids')
                        //     ->label('Gred')
                        //     ->options(function (Get $get) {

                        //         $jawatanId = $get('jawatan_id');

                        //         if (blank($jawatanId)) {
                        //             return [];
                        //         }

                        //         return Jawatan_Gred::query()
                        //             ->where('jawatan_id', $jawatanId)
                        //             ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                        //             ->pluck('greds.kod_gred', 'greds.id')
                        //             ->toArray();
                        //     })
                        //     ->live()
                        //     ->searchable()
                        //     ->preload()
                        //     ->dehydrated(false)
                        //     ->multiple()
                        //     ->disabled(fn(Get $get) => blank($get('jawatan_id')))
                        //     ->afterStateHydrated(function ($state, Get $get, Set $set) {

                        //         $jawatanGredId = $get('jawatan_gred_id');

                        //         if (!$jawatanGredId) {
                        //             return;
                        //         }

                        //         $jawatanGred = Jawatan_Gred::find($jawatanGredId);

                        //         if (!$jawatanGred) {
                        //             return;
                        //         }

                        //         $set('gred_id', $jawatanGred->gred_id);
                        //     })
                        //     ->afterStateUpdated(function ($state, Get $get, Set $set) {

                        //         if (blank($state)) {
                        //             return;
                        //         }

                        //         $jawatanGred = Jawatan_Gred::query()
                        //             ->where('jawatan_id', $get('jawatan_id'))
                        //             ->where('gred_id', $state)
                        //             ->first();

                        //         $set('jawatan_gred_id', $jawatanGred?->id);

                        //         // 🔥 reset dependent fields
                        //         $set('pegawai_id', null);
                        //         $set('butiran', null);
                        //     }),
                        // Hidden::make('jawatan_gred_id'),


                        //     Select::make('ptj_id')
                        //         ->label('PTJ')
                        //         ->options(
                        //             Ptj::pluck('nama_ptj', 'id')
                        //         )
                        //         ->searchable()
                        //         ->preload()
                        //         ->required()
                        //         ->columnSpanFull(),

                        //     Select::make('pegawai_id')
                        //         ->label('Pegawai')
                        //         ->options(function (Get $get) {

                        //             $jawatanGredId = $get('jawatan_gred_id');

                        //             if (blank($jawatanGredId)) {
                        //                 return [];
                        //             }

                        //             return Pegawai::query()
                        //                 ->where('jawatan_gred_id', $jawatanGredId)
                        //                 ->orderBy('nama')
                        //                 ->pluck('nama', 'id')
                        //                 ->toArray();
                        //         })

                        //         // 🔥 GLOBAL + LOCAL VALIDATION
                        //         ->rules([
                        //             function (Get $get) {
                        //                 return function (string $attribute, $value, $fail) use ($get) {

                        //                     if (blank($value))
                        //                         return;

                        //                     // 1. CHECK DB (global uniqueness)
                        //                     $existsInDb = WaranJawatan::query()
                        //                         ->where('pegawai_id', $value)
                        //                         ->exists();

                        //                     $currentId = $get('id');

                        //                     if ($existsInDb && !$currentId) {
                        //                         $fail('Pegawai ini sudah mempunyai waran.');
                        //                         return;
                        //                     }

                        //                     // 2. CHECK REPEATER (same form)
                        //                     $rows = $get('../../waranJawatans') ?? [];

                        //                     $count = collect($rows)
                        //                         ->pluck('pegawai_id')
                        //                         ->filter(fn($id) => $id == $value)
                        //                         ->count();

                        //                     if ($count > 1) {
                        //                         $fail('Pegawai ini telah dipilih dalam baris lain.');
                        //                     }
                        //                 };
                        //             },
                        //         ])

                        //         ->disableOptionWhen(function ($value, Get $get) {

                        //             $current = $get('pegawai_id');

                        //             // allow current value (edit mode safety)
                        //             if ((int) $current === (int) $value) {
                        //                 return false;
                        //             }

                        //             // 1. check repeater duplicates
                        //             $rows = $get('../../waranJawatan') ?? [];

                        //             $selected = collect($rows)
                        //                 ->pluck('pegawai_id')
                        //                 ->filter()
                        //                 ->toArray();

                        //             if (in_array($value, $selected)) {
                        //                 return true;
                        //             }

                        //             return WaranJawatan::query()
                        //                 ->where('pegawai_id', $value)
                        //                 ->exists();
                        //         })
                        //         ->searchable()
                        //         ->preload()
                        //         ->live()
                        //         ->disabled(fn(Get $get) => blank($get('jawatan_gred_id')))
                        //         ->columnSpanFull(),
                        // ])
                        // ->action(function (array $data, $record) {

                        //     $waranJawatan = $record->waranJawatan()->create([
                        //         'jawatan_id' => $data['jawatan_id'],
                        //         'ptj_id' => $data['ptj_id'],
                        //         'pegawai_id' => $data['pegawai_id'] ?? null,
                        //         'aktiviti_id' => $data['aktiviti_id'],
                        //         'butiran' => $data['Butiran'],
                        //         'gred_ids' => $data['gred_ids'] ?? [],

                        //     ]);

                        //     // $waranJawatan->gred()->attach($data['gred_ids'] ?? []);

                        //     Notification::make()
                        //         ->title('Berjaya')
                        //         ->body('Jawatan berjaya ditambah.')
                        //         ->success()
                        //         ->send();
                        // }),


                        // Action::make('tambahAktiviti')
                        //     ->label('Tambah Aktiviti')
                        //     ->modalHeading('Tambah Aktiviti')
                        //     ->form([
                        //         // form fields here
                        //     ])
                        //     ->action(function (array $data) {
                        //         // save logic
                        //     }),

                        // ])
                    ])
                    ->columns(2)
                    ->columnSpanFull(),



                // Section::make('Senarai Jawatan')
                //     ->columns(3)
                //     ->columnSpanFull()
                //     ->schema([
                //         Grid::make(3)
                //             ->schema([

                //                 TextEntry::make('jik')
                //                     ->label('Jumlah Jawatan')
                //                     // ->badge()
                //                     ->color('primary')
                //                     ->extraAttributes([
                //                         'class' => 'text-center p-4 rounded-xl bg-blue-50',
                //                     ])
                //                     ->state(fn($record) => $record?->jik),

                //                 TextEntry::make('isi')
                //                     ->label('Jawatan Diisi')
                //                     // ->badge()
                //                     ->color('warning')
                //                     ->extraAttributes([
                //                         'class' => 'text-center p-4 rounded-xl bg-yellow-50',
                //                     ])
                //                     ->state(fn($record) => $record?->waranJawatan()->count()),

                //                 TextEntry::make('kekosongan')
                //                     ->label('Kekosongan')
                //                     // ->badge()
                //                     ->color('success')
                //                     ->extraAttributes([
                //                         'class' => 'text-center p-4 rounded-xl bg-green-50',
                //                     ])
                //                     ->state(fn($record) => $record?->jik - $record?->waranJawatan()->count()),
                //             ]),

                //         // ViewField::make('waran_jawatans_table')
                //         //     ->view('filament.custom.warans.senarai-jawatan-table')
                //         //     ->columnSpanFull(),
                //     ]),

                //                 Action::make('editJawatan')
                // ->label('Edit')
                // ->icon('heroicon-o-pencil')

                // ->fillForm(fn (WaranJawatan $record) => $record->toArray())

                // ->form([
                //     Select::make('jawatan_id')
                //         ->options(Jawatan::pluck('desc_jawatan', 'id')),

                //     Select::make('ptj_id')
                //         ->options(Ptj::pluck('nama_ptj', 'id')),

                //     Select::make('gred_ids')
                //         ->multiple()
                //         ->options(Gred::pluck('kod_gred', 'id')),

                //     TextInput::make('butiran'),
                // ])

                // ->action(function (array $data, WaranJawatan $record) {

                //     $record->update([
                //         'jawatan_id' => $data['jawatan_id'],
                //         'ptj_id' => $data['ptj_id'],
                //         'butiran' => $data['butiran'],
                //         'gred_ids' => $data['gred_ids'] ?? [],
                //     ]);
                // })



            ]);


    }
    public function test()
    {
        Notification::make()
            ->title('Livewire working')
            ->success()
            ->send();
    }
}
