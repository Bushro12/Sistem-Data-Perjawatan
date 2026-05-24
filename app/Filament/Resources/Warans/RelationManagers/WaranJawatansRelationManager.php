<?php

namespace App\Filament\Resources\Warans\RelationManagers;

use App\Models\Bahagian;
use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
use App\Models\Program;
use App\Models\Ptj;
use App\Models\Subunit;
use App\Models\Unit;
use App\Models\WaranJawatan;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Attributes\Reactive;

class WaranJawatansRelationManager extends RelationManager
{
    protected static string $relationship = 'waranJawatan';
    public ?int $aktivitiFilter = null;
    public ?string $butiranFilter = null;

    public string $viewMode = 'active';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('aktiviti_id')
                    // ->relationship('aktiviti', 'nama_aktiviti')
                    ->required()
                    ->options(function () {

                        return Program::with('aktiviti')
                            ->orderBy('nama_program')
                            ->get()
                            ->mapWithKeys(function ($program) {

                                return [
                                    $program->nama_program => $program->aktiviti
                                        ->mapWithKeys(function ($aktiviti) {
                                            return [
                                                $aktiviti->id => $aktiviti->no_aktivit . ' - ' . $aktiviti->nama_aktiviti
                                            ];
                                        })
                                        ->toArray(),
                                ];

                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->columns(1),

                TextInput::make('butiran')
                    ->required()
                    ->maxLength(255),

                Select::make('jawatan_ids')
                    ->label('Jawatan')
                    ->multiple()
                    ->options(
                        Jawatan::orderBy('desc_jawatan')
                            ->pluck('desc_jawatan', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('gred_ids')
                    ->label('Gred')
                    ->multiple()
                    ->options(function (Get $get) {

                        $jawatanIds = $get('jawatan_ids');

                        if (blank($jawatanIds)) {
                            return [];
                        }

                        return Jawatan_Gred::query()
                            ->whereIn('jawatan_id', $jawatanIds)
                            ->join('greds', 'jawatan__greds.gred_id', '=', 'greds.id')
                            ->orderBy('greds.kod_gred')
                            ->pluck('greds.kod_gred', 'greds.id')
                            ->toArray();
                    })
                    ->disabled(fn(Get $get) => blank($get('jawatan_ids')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->live(),

                Select::make('ptj_id')
                    ->label('PTJ')
                    ->options(
                        Ptj::pluck('nama_ptj', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                Select::make('bahagian_id')
                    ->label('Bahagian')
                    ->options(function (Get $get) {

                        $ptjId = $get('ptj_id');

                        if (blank($ptjId)) {
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
                    ->disabled(fn(Get $get) => blank($get('ptj_id')))
                    ->columnSpanFull(),

                Select::make('unit_id')
                    ->label('Unit')
                    ->options(function (Get $get) {
                        $bahagianId = $get('bahagian_id');

                        if (blank($bahagianId)) {
                            return [];
                        }

                        return Unit::query()
                            ->where('bahagian_id', $bahagianId)
                            ->orderBy('nama_unit')
                            ->pluck('nama_unit', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->live()
                    ->preload(),

                Select::make('subunit_id')
                    ->label('Subunit')
                    ->options(function (Get $get) {
                        $unitId = $get('unit_id');

                        if (blank($unitId)) {
                            return [];
                        }

                        return Subunit::query()
                            ->where('unit_id', $unitId)
                            ->orderBy('nama_subunit')
                            ->pluck('nama_subunit', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Select::make('pegawai_id')
                    ->label('Pegawai')
                    ->options(function (Get $get) {

                        $jawatanIds = $get('jawatan_ids');
                        $gredIds = $get('gred_ids');

                        if (blank($jawatanIds) || blank($gredIds)) {
                            return [];
                        }

                        $jawatanGredIds = Jawatan_Gred::query()
                            ->whereIn('jawatan_id', $jawatanIds)
                            ->whereIn('gred_id', $gredIds)
                            ->pluck('id');

                        return Pegawai::query()
                            ->whereIn('jawatan_gred_id', $jawatanGredIds)
                            ->whereNotIn('id', function ($q) {
                                $q->select('pegawai_id')
                                    ->from('waran_jawatans')
                                    ->whereNotNull('pegawai_id');
                            })
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->live()
                    ->preload()
                    ->columnSpanFull()
                    ->disabled(function (Get $get) {
                        return blank($get('jawatan_ids'))
                            || blank($get('gred_ids'));
                    }),

                Checkbox::make('is_kup')
                    ->label('Khas Untuk Penyandang (KUP)'),

                Textarea::make('catatan_jawatan')
                    ->label('Catatan')

                    ->columnSpanFull(),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('ptj')

            ->modifyQueryUsing(function (Builder $query) {

                $waran = $this->getOwnerRecord();

                $query = WaranJawatan::query()->withoutGlobalScopes();

                if ($waran->jenis !== 'tolak') {

                    return $query
                        ->where('waran_id', $waran->id)
                        ->where('status', '!=', 'deleted')
                        ->orderByRaw("status = 'removed' ASC")
                        ->orderBy('id', 'asc');
                }

                if ($this->viewMode === 'active') {
                    return $query->whereNull('deleted_at')
                        ->where('status', '!=', 'deleted');
                }

                if ($this->viewMode === 'inactive') {
                    return $query
                        ->where('waran_tolak_id', $waran->id)
                        ->whereNotNull('deleted_at')
                        ->where('status', 'removed');
                }

                return $query;

            })


            ->columns([

                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),
                TextColumn::make('butiran')
                    ->label('Butiran')
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),
                TextColumn::make('aktiviti')
                    ->label('Aktiviti')
                    ->formatStateUsing(function ($record) {
                        return $record->aktiviti
                            ? $record->aktiviti->no_aktivit . ' - ' . $record->aktiviti->nama_aktiviti
                            : '-';
                    })
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    )
                    ->wrap(),
                TextColumn::make('jawatan_gred_display')
                    ->label('Jawatan / Gred')
                    ->state(function ($record) {
                        return $record->jawatan_list . '<br>' . $record->gred_list;
                    })
                    ->html()
                    ->wrap()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    ),

                TextColumn::make('ptj.nama_ptj')
                    ->searchable()
                    ->color(
                        fn($record) =>
                        $record->status === 'removed' ? 'gray' : 'default'
                    )
                    ->wrap(),
                // TextColumn::make('pegawai.nama')
                //     ->label('Nama Penyandang')
                //     ->searchable()
                //     ->color(
                //         fn($record) =>
                //         $record->status === 'removed' ? 'gray' : 'default'
                //     )
                //     ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->size('lg')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'removed' => 'Dibuang',
                        default => 'Aktif',
                    })
                    ->color(fn($state) => $state === 'removed' ? 'danger' : 'success'),

            ])

            ->filters([

                // TrashedFilter::make(),

                SelectFilter::make('aktiviti_id')
                    ->label('Aktiviti')
                    ->options(function () {

                        return Program::with('aktiviti')
                            ->orderBy('nama_program')
                            ->get()
                            ->mapWithKeys(function ($program) {

                                return [
                                    $program->nama_program => $program->aktiviti
                                        ->mapWithKeys(function ($aktiviti) {
                                            return [
                                                $aktiviti->id =>
                                                    $aktiviti->no_aktivit . ' - ' . $aktiviti->nama_aktiviti
                                            ];
                                        })
                                        ->toArray(),
                                ];

                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'tolak'),

                SelectFilter::make('butiran')
                    ->label('Butiran')
                    ->options(
                        WaranJawatan::query()
                            ->pluck('butiran', 'butiran')
                            ->unique()
                            ->toArray()
                    )
                    ->searchable()
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'tolak'),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->filtersApplyAction(
                fn(Action $action) => $action->label('Cari Jawatan')
            )

            ->headerActions([
                CreateAction::make()
                    ->label('Tambah jawatan')
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'tambah'),

                // Action::make('active')
                //     ->label('Aktif')
                //     ->color(fn() => $this->viewMode === 'active' ? 'primary' : 'gray')
                //     ->action(fn($livewire) => $livewire->viewMode = 'active')
                //     ->button()
                //     ->visible(fn() => $this->getOwnerRecord()->jenis === 'tolak'),

                // Action::make('inactive')
                //     ->label('Dibuang')
                //     ->color(fn() => $this->viewMode === 'inactive' ? 'danger' : 'gray')
                //     ->action(fn($livewire) => $livewire->viewMode = 'inactive')
                //     ->button()
                //     ->visible(fn() => $this->getOwnerRecord()->jenis === 'tolak'),

                Action::make('viewModeTabs')
                    ->label('')
                    ->view('filament.custom.warans.view-mode-tabs', [
                        'viewMode' => fn($livewire) => $livewire->viewMode,
                    ])
                    ->visible(fn() => $this->getOwnerRecord()->jenis === 'tolak'),

            ])


            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => $record->status !== 'removed'),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->actions([

                ViewAction::make('view')
                    ->color('info'),

                EditAction::make()
                    ->visible(
                        fn($record) =>
                        $this->getOwnerRecord()->jenis === 'tambah'
                        && $record->status === 'active'
                    ),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) =>
                        $this->getOwnerRecord()->jenis === 'tambah'
                        && $record->status === 'active'
                    )
                    ->action(function ($record) {

                        $record->update([
                            'status' => 'deleted',
                        ]);
                        $record->delete();
                    }),


                Action::make('remove')
                    ->label('Buang Jawatan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) =>
                        $this->getOwnerRecord()->jenis === 'tolak'
                        && $record->status === 'active'
                    )
                    ->action(function ($record) {

                        $waran = $this->getOwnerRecord();

                        $record->update([
                            'waran_tolak_id' => $waran->id,
                            'status' => 'removed',
                        ]);

                        $record->delete();
                    }),

                Action::make('restore')
                    ->label('Undo Buang')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) =>
                        $this->getOwnerRecord()->jenis === 'tolak'
                        && $record->status === 'removed'
                    )
                    ->action(function ($record) {

                        $record->restore();

                        $record->update([
                            'waran_tolak_id' => null,
                            'status' => 'active',
                        ]);
                    }),
            ]);
    }
}
