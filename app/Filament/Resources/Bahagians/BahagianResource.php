<?php

namespace App\Filament\Resources\Bahagians;

use App\Filament\Resources\Bahagians\Pages\CreateBahagian;
use App\Filament\Resources\Bahagians\Pages\EditBahagian;
use App\Filament\Resources\Bahagians\Pages\ListBahagians;
use App\Filament\Resources\Bahagians\Schemas\BahagianForm;
use App\Filament\Resources\Bahagians\Tables\BahagiansTable;
use App\Models\Bahagian;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BahagianResource extends Resource
{
    protected static ?string $model = Bahagian::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_bahagian';

    protected static ?string $navigationLabel = 'Bahagian';
    protected static ?string $modelLabel = 'Bahagian';

    protected static ?string $pluralModelLabel = 'Bahagian';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    public static function form(Schema $schema): Schema
    {
        return BahagianForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BahagiansTable::configure($table);
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
            'index' => ListBahagians::route('/'),
            'create' => CreateBahagian::route('/create'),
            'edit' => EditBahagian::route('/{record}/edit'),
        ];
    }
}
