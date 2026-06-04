<?php

namespace App\Filament\Resources\Pencens;

use App\Filament\Resources\Pencens\Pages\CreatePencen;
use App\Filament\Resources\Pencens\Pages\EditPencen;
use App\Filament\Resources\Pencens\Pages\ListPencens;
use App\Filament\Resources\Pencens\Schemas\PencenForm;
use App\Filament\Resources\Pencens\Tables\PencensTable;
use App\Models\Pencen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PencenResource extends Resource
{
    protected static ?string $model = Pencen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?string $modelLabel = 'Penamatan Perkhidmatan';

    protected static ?string $pluralModelLabel = 'Penamatan Perkhidmatan';

    protected static ?string $navigationLabel = 'Penamatan Perkhidmatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Pegawai';

    protected static ?int $navigationSort = 13;


    public static function form(Schema $schema): Schema
    {
        return PencenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PencensTable::configure($table);
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
            'index' => ListPencens::route('/'),
            'create' => CreatePencen::route('/create'),
            'edit' => EditPencen::route('/{record}/edit'),
        ];
    }
}
