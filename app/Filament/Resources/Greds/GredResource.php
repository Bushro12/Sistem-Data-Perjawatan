<?php

namespace App\Filament\Resources\Greds;

use App\Filament\Resources\Greds\Pages\CreateGred;
use App\Filament\Resources\Greds\Pages\EditGred;
use App\Filament\Resources\Greds\Pages\ListGreds;
use App\Filament\Resources\Greds\Schemas\GredForm;
use App\Filament\Resources\Greds\Tables\GredsTable;
use App\Models\Gred;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GredResource extends Resource
{
    protected static ?string $model = Gred::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'desc_gred';

    protected static ?string $navigationLabel ='Gred';

    protected static ?string $modelLabel ='Gred';

    protected static ?string $pluralModelLabel ='Gred';

    protected static string |\UnitEnum|null $navigationGroup = 'Kawalan';
    protected static ?int $navigationSort = 26;

    public static function form(Schema $schema): Schema
    {
        return GredForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GredsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGreds::route('/'),
            'create' => CreateGred::route('/create'),
            'edit' => EditGred::route('/{record}/edit'),
        ];
    }
}
