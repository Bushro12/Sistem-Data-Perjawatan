<?php

namespace App\Filament\Resources\Subunits;

use App\Filament\Resources\Subunits\Pages\CreateSubunit;
use App\Filament\Resources\Subunits\Pages\EditSubunit;
use App\Filament\Resources\Subunits\Pages\ListSubunits;
use App\Filament\Resources\Subunits\Schemas\SubunitForm;
use App\Filament\Resources\Subunits\Tables\SubunitsTable;
use App\Models\Subunit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class SubunitResource extends Resource
{
    protected static ?string $model = Subunit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $recordTitleAttribute = 'nama_subunit';

    protected static ?string $navigationLabel = 'KD / KKIA / Wad / Klinik';

    protected static ?string $modelLabel = 'KD / KKIA / Wad / Klinik';

    protected static ?string $pluralModelLabel = 'KD / KKIA / Wad / Klinik';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    protected static ?int $navigationSort = 24;

    public static function form(Schema $schema): Schema
    {
        return SubunitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubunitsTable::configure($table);
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
            'index' => ListSubunits::route('/'),
            'create' => CreateSubunit::route('/create'),
            'edit' => EditSubunit::route('/{record}/edit'),
        ];
    }

//     public static function getEloquentQuery(): Builder
// {
//     return parent::getEloquentQuery() 
//         ->whereIn('id', ...)
//         ->whereHas('unit', fn ($q) => $q->where('ptj_id', 2)); // Fix here
// }

// use Illuminate\Database\Eloquent\Builder;

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery();
}

}
