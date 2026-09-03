<?php

namespace App\Filament\Resources\Subunits\Schemas;

use App\Models\Bahagian;
use App\Models\Dun;
use App\Models\Parlimen;
use App\Models\Ptj;
use App\Models\Subunit;
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

class SubunitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Sub Unit')
                    ->schema([

                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->required()
                            ->options(
                                Ptj::query()
                                    ->orderBy('nama_ptj')
                                    ->pluck('nama_ptj', 'id')
                            )
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->ptj_id
                                );
                            })
                            ->live()
                            ->searchable()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record === null)
                            ->columnSpanFull(),

                        TextInput::make('ptj_id')
                            ->label('PTJ')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->ptj?->nama_ptj
                                );
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null)
                            ->columnSpanFull(),

                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->required()
                            ->options(function (Get $get) {
                                $ptjId = $get('ptj_id');

                                return $ptjId
                                    ? Bahagian::where('ptj_id', $ptjId)
                                        ->orderBy('nama_bahagian')
                                        ->pluck('nama_bahagian', 'id')
                                    : [];
                            })
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian_id
                                );
                            })
                            ->live()
                            ->searchable()
                            ->visible(fn ($record) => $record === null)
                            ->dehydrated(false),

                        TextInput::make('bahagian_id')
                            ->label('Bahagian')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->nama_bahagian
                                );
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null),

                        Select::make('unit_id')
                            ->label('Unit')
                            ->required()
                            ->options(function (Get $get) {
                                $bahagianId = $get('bahagian_id');

                                return $bahagianId
                                    ? Unit::where('bahagian_id', $bahagianId)
                                        ->orderBy('nama_unit')
                                        ->pluck('nama_unit', 'id')
                                    : [];
                            })
                            ->default(fn ($record) => $record?->unit_id)
                            ->visible(fn ($record) => $record === null)

                            ->searchable(),

                        TextInput::make('unit_id')
                            ->label('Unit')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->nama_unit
                                );
                            })
                            ->readOnly()
                            ->visible(fn ($record) => $record !== null)
                            ->dehydrated(false),

                        Repeater::make('subunits')
                            ->label('Senarai Sub Unit')
                            ->columnSpanFull()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Sub Unit')
                            ->addAction(fn (Action $action) => $action->color('info')->icon('heroicon-m-plus'))
                            ->columns(2)
                            ->afterStateHydrated(function ($component, ?array $state, $record): void {
                                if ($record && blank($state)) {
                                    $items = Subunit::where('unit_id', $record->unit_id)
                                        ->orderBy('nama_subunit')
                                        ->get()
                                        ->map(fn (Subunit $s): array => [
                                            'id' => $s->id,
                                            'nama_subunit' => $s->nama_subunit,
                                            'parlimen_id' => $s->parlimen_id,
                                            'dun_id' => $s->dun_id,
                                        ])
                                        ->toArray();
                                    $component->state($items);
                                }
                            })
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('nama_subunit')
                                    ->label('Sub Unit')
                                    ->required()
                                    ->distinct()
                                    ->unique(
                                        table: 'subunits',
                                        column: 'nama_subunit',
                                        ignorable: fn (Get $get) => filled($get('id')) ? Subunit::find($get('id')) : null,
                                        modifyRuleUsing: function (Unique $rule, Get $get, Component $component): Unique {
                                            $unitId = $get('../../unit_id');

                                            if (blank($unitId)) {
                                                $record = $component->getRecord();

                                                if ($record) {
                                                    $unitId = $record->unit_id;
                                                }
                                            }

                                            if (blank($unitId)) {
                                                $id = $get('id');
                                                if (filled($id)) {
                                                    $unitId = Subunit::find($id)?->unit_id;
                                                }
                                            }

                                            if (filled($unitId)) {
                                                $rule->where('unit_id', $unitId);
                                            }

                                            return $rule;
                                        }
                                    )
                                    ->validationMessages([
                                        'distinct' => 'Nama sub unit tidak boleh duplikat dalam senarai ini.',
                                        'unique' => 'Nama sub unit telah wujud untuk PTJ, Bahagian dan Unit ini.',
                                    ])
                                    ->dehydrateStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '')
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                                    ->columnSpanFull(),
                                Select::make('parlimen_id')
                                    ->label('Parlimen')
                                    ->required()
                                    ->options(fn (): array => Parlimen::query()->orderBy('nama_parlimen')->pluck('nama_parlimen', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('dun_id', null)),
                                Select::make('dun_id')
                                    ->label('DUN')
                                    ->required()
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
                            ->itemLabel(fn (array $state): ?string => filled($state['nama_subunit'] ?? null) ? strtoupper($state['nama_subunit']) : 'Sub unit baharu')
                            ->collapsed()
                            ->collapsible()
                            ->deleteAction(function (Action $action): Action {
                                return $action
                                    ->requiresConfirmation()
                                    ->modalHeading(function (array $arguments, Repeater $component): string {
                                        $items = $component->getRawState();
                                        $item = $items[$arguments['item']] ?? [];
                                        $nama = trim((string) ($item['nama_subunit'] ?? ''));

                                        return $nama !== '' ? "Padam {$nama}?" : 'Padam sub unit ini?';
                                    })
                                    ->modalDescription('Adakah anda pasti mahu memadam sub unit ini? Tindakan ini tidak boleh dibatalkan.')
                                    ->modalSubmitActionLabel('Ya, Padam')
                                    ->modalCancelActionLabel('Batal');
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
