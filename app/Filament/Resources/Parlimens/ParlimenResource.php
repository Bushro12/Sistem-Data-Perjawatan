<?php

namespace App\Filament\Resources\Parlimens;

use App\Filament\Resources\Parlimens\Pages\CreateParlimen;
use App\Filament\Resources\Parlimens\Pages\EditParlimen;
use App\Filament\Resources\Parlimens\Pages\ListParlimens;
use App\Filament\Resources\Parlimens\Schemas\ParlimenForm;
use App\Filament\Resources\Parlimens\Tables\ParlimensTable;
use App\Models\Parlimen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParlimenResource extends Resource
{
    protected static ?string $model = Parlimen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_parlimen';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';
    protected static ?int $navigationSort = 29;

    public static function form(Schema $schema): Schema
    {
        return ParlimenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParlimensTable::configure($table);
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
            'index' => ListParlimens::route('/'),
            'create' => CreateParlimen::route('/create'),
            'edit' => EditParlimen::route('/{record}/edit'),
        ];
    }
}
