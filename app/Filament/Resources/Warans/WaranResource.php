<?php

namespace App\Filament\Resources\Warans;

use App\Filament\Resources\Warans\Pages\CreateWaran;
use App\Filament\Resources\Warans\Pages\CustomTable;
use App\Filament\Resources\Warans\Pages\EditWaran;
use App\Filament\Resources\Warans\Pages\ListWarans;
use App\Filament\Resources\Warans\RelationManagers\WaranJawatansRelationManager;
use App\Filament\Resources\Warans\Schemas\WaranForm;
use App\Filament\Resources\Warans\Tables\WaransTable;
use App\Models\Waran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WaranResource extends Resource
{
    protected static ?string $model = Waran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'no_waran';

    protected static ?string $navigationLabel = 'Waran';

    protected static ?string $modelLabel = 'Waran';

    protected static ?string $pluralModelLabel = 'Waran';

    protected static string|\UnitEnum|null $navigationGroup = 'Buku Waran';

    public static function form(Schema $schema): Schema
    {
        return WaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaransTable::configure($table);
    }

public static function getRelations(): array
{
    return [
        WaranJawatansRelationManager::class,
    ];
}

    public static function getPages(): array
    {
        return [
            'custom' =>CustomTable::route('/custom'),
            'index' => ListWarans::route('/'),
            'create' => CreateWaran::route('/create'),
            'edit' => EditWaran::route('/{record}/edit'),
        ];
    }

    protected function getListeners(): array
    {
        return [
            'setWaran' => 'setWaran',
        ];
    }

    public function setWaran($id): void
    {
        $this->form()->fill([
            'selected_waran_id' => $id,
            'catatan' => Waran::find($id)?->catatan,
        ]);
    }
}
