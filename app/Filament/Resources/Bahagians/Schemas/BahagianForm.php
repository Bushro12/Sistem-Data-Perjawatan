<?php

namespace App\Filament\Resources\Bahagians\Schemas;

use App\Models\Bahagian;
use App\Models\Dun;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class BahagianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Bahagian')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('Ptj')
                            ->relationship('ptj', 'nama_ptj')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn ($record) => $record === null)
                            ->columnSpanFull(),

                        TextInput::make('ptj_id')
                            ->label('Ptj')
                            ->afterStateHydrated(function ($component, $state, $record): void {
                                $component->state($record?->ptj?->nama_ptj);
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null)
                            ->columnSpanFull(),

                        Repeater::make('bahagians')
                            ->label('Senarai Bahagian')
                            ->columnSpanFull()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Bahagian')
                            ->addAction(fn (Action $action) => $action->color('info')->icon('heroicon-m-plus'))
                            ->afterStateHydrated(function ($component, ?array $state, $record): void {
                                if ($record && blank($state)) {
                                    $items = Bahagian::where('ptj_id', $record->ptj_id)
                                        ->orderBy('nama_bahagian')
                                        ->get()
                                        ->map(fn (Bahagian $b): array => [
                                            'id' => $b->id,
                                            'nama_bahagian' => $b->nama_bahagian,
                                        ])
                                        ->toArray();
                                    $component->state($items);
                                }
                            })
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('nama_bahagian')
                                    ->label('Nama Bahagian')
                                    ->required()
                                    ->distinct()
                                    ->unique(
                                        table: 'bahagians',
                                        column: 'nama_bahagian',
                                        ignorable: fn (Get $get) => filled($get('id')) ? Bahagian::find($get('id')) : null,
                                        modifyRuleUsing: function (Unique $rule, Get $get, Component $component): Unique {
                                            $ptjId = $get('../../ptj_id');

                                            if (blank($ptjId)) {
                                                $record = $component->getRecord();

                                                if ($record) {
                                                    $ptjId = $record->ptj_id;
                                                }
                                            }

                                            if (blank($ptjId)) {
                                                $id = $get('id');
                                                if (filled($id)) {
                                                    $ptjId = Bahagian::find($id)?->ptj_id;
                                                }
                                            }

                                            if (filled($ptjId)) {
                                                $rule->where('ptj_id', $ptjId);
                                            }

                                            $rule->whereNull('deleted_at');

                                            return $rule;
                                        }
                                    )
                                    ->validationMessages([
                                        'distinct' => 'Nama bahagian tidak boleh duplikat dalam senarai ini.',
                                        'unique' => 'Nama bahagian telah wujud untuk PTJ ini.',
                                    ])
                                    ->dehydrateStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '')
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['nama_bahagian'] ?? null) ? strtoupper($state['nama_bahagian']) : 'Bahagian baharu')
                            ->collapsed()
                            ->collapsible()
                            ->deleteAction(function (Action $action): Action {
                                return $action
                                    ->requiresConfirmation()
                                    ->modalHeading(function (array $arguments, Repeater $component): string {
                                        $items = $component->getRawState();
                                        $item = $items[$arguments['item']] ?? [];
                                        $nama = trim((string) ($item['nama_bahagian'] ?? ''));

                                        return $nama !== '' ? "Padam {$nama}?" : 'Padam bahagian ini?';
                                    })
                                    ->modalDescription('Adakah anda pasti mahu memadam bahagian ini? Tindakan ini tidak boleh dibatalkan.')
                                    ->modalSubmitActionLabel('Ya, Padam')
                                    ->modalCancelActionLabel('Batal');
                            }),

                        // Repeater::make('units')

                        //     ->label('Unit')
                        //     ->relationship()
                        //     ->addActionLabel('Tambah Unit')
                        //     ->addAction(function (Action $action) {
                        //         return $action
                        //             ->color('info')
                        //             ->icon('heroicon-m-plus');
                        //     })
                        //     ->simple(
                        //         TextInput::make('nama_unit')
                        //             ->required()
                        //             ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : null)
                        //             ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                        //     )
                        //     ->columnSpanFull(),

                        // Select::make('parlimen_id')
                        //     ->label('Parlimen')
                        //     ->relationship('parlimen', 'nama_parlimen')
                        //     ->searchable()
                        //     ->preload()
                        //     ->live()
                        //     ->afterStateUpdated(fn(Set $set) => $set('dun_id', null)),

                        // Select::make('dun_id')
                        //     ->label('DUN')
                        //     ->searchable()
                        //     ->options(function (Get $get): array {
                        //         $parlimenId = $get('parlimen_id');
                        //         if (blank($parlimenId))
                        //             return [];
                        //         return Dun::where('parlimen_id', $parlimenId)
                        //             ->pluck('nama_dun', 'id')
                        //             ->toArray();
                        //     })
                        //     ->disabled(fn(Get $get) => blank($get('parlimen_id')))
                        //     ->helperText('Sila pilih Parlimen dahulu'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
