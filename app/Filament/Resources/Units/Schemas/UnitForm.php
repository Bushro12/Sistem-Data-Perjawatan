<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Bahagian;
use App\Models\Dun;
use App\Models\Parlimen;
use App\Models\Ptj;
use App\Models\Unit;
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

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Unit')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->required()
                            ->options(
                                Ptj::query()
                                    ->orderBy('nama_ptj')
                                    ->pluck('nama_ptj', 'id')
                            )
                            ->live()
                            ->searchable()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record === null)
                            ->columnSpanFull(),

                        TextInput::make('ptj_id')
                            ->label('PTJ')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->bahagian?->ptj?->nama_ptj
                                );
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null)
                            ->columnSpanFull(),

                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->options(function (Get $get) {
                                return Bahagian::query()
                                    ->where('ptj_id', $get('ptj_id'))
                                    ->orderBy('nama_bahagian')
                                    ->pluck('nama_bahagian', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record === null),

                        TextInput::make('bahagian_id')
                            ->label('Bahagian')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->bahagian?->nama_bahagian
                                );
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null)
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        Repeater::make('units')
                            ->label('Senarai Unit')
                            ->columnSpanFull()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Unit')
                            ->addAction(fn (Action $action) => $action
                                ->color('info')
                                ->icon('heroicon-m-plus'))
                            ->columns(2)
                            ->afterStateHydrated(function ($component, ?array $state, $record): void {
                                if ($record && blank($state)) {
                                    $units = Unit::where('bahagian_id', $record->bahagian_id)
                                        ->orderBy('nama_unit')
                                        ->get()
                                        ->map(fn (Unit $u): array => [
                                            'id' => $u->id,
                                            'nama_unit' => $u->nama_unit,
                                            'parlimen_id' => $u->parlimen_id,
                                            'dun_id' => $u->dun_id,
                                        ])
                                        ->toArray();

                                    $component->state($units);
                                }
                            })
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('nama_unit')
                                    ->label('Nama Unit')
                                    ->required()
                                    ->distinct()
                                    ->unique(
                                        table: 'units',
                                        column: 'nama_unit',
                                        ignorable: fn (Get $get) => filled($get('id')) ? Unit::find($get('id')) : null,
                                        modifyRuleUsing: function (Unique $rule, Get $get, Component $component): Unique {
                                            $bahagianId = $get('../../bahagian_id');

                                            if (blank($bahagianId)) {
                                                $record = $component->getRecord();

                                                if ($record) {
                                                    $bahagianId = $record->bahagian_id;
                                                }
                                            }

                                            if (blank($bahagianId)) {
                                                $id = $get('id');
                                                if (filled($id)) {
                                                    $bahagianId = Unit::find($id)?->bahagian_id;
                                                }
                                            }

                                            if (filled($bahagianId)) {
                                                $rule->where('bahagian_id', $bahagianId);
                                            }

                                            $rule->whereNull('deleted_at');

                                            return $rule;
                                        }
                                    )
                                    ->validationMessages([
                                        'distinct' => 'Nama unit tidak boleh duplikat dalam senarai ini.',
                                        'unique' => 'Nama unit telah wujud untuk PTJ dan Bahagian ini.',
                                    ])
                                    ->dehydrateStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '')
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                                    ->columnSpanFull(),
                                Select::make('parlimen_id')
                                    ->label('Parlimen')
                                    ->options(fn (): array => Parlimen::query()->orderBy('nama_parlimen')->pluck('nama_parlimen', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('dun_id', null)),
                                Select::make('dun_id')
                                    ->label('DUN')
                                    ->searchable()
                                    ->preload()
                                    ->options(function (Get $get): array {
                                        $parlimenId = $get('parlimen_id');
                                        if (blank($parlimenId)) {
                                            return [];
                                        }

                                        return Dun::where('parlimen_id', $parlimenId)
                                            ->pluck('nama_dun', 'id')
                                            ->toArray();
                                    })
                                    ->disabled(fn (Get $get): bool => blank($get('parlimen_id')))
                                    ->helperText('Sila pilih Parlimen dahulu'),
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['nama_unit'] ?? null) ? strtoupper($state['nama_unit']) : 'Unit baharu')
                            ->collapsed(false)
                            ->deleteAction(function (Action $action): Action {
                                return $action
                                    ->requiresConfirmation()
                                    ->modalHeading(function (array $arguments, Repeater $component): string {
                                        $items = $component->getRawState();
                                        $item = $items[$arguments['item']] ?? [];
                                        $nama = trim((string) ($item['nama_unit'] ?? ''));

                                        return $nama !== '' ? "Padam {$nama}?" : 'Padam unit ini?';
                                    })
                                    ->modalDescription('Adakah anda pasti mahu memadam unit ini? Tindakan ini tidak boleh dibatalkan.')
                                    ->modalSubmitActionLabel('Ya, Padam')
                                    ->modalCancelActionLabel('Batal');
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
