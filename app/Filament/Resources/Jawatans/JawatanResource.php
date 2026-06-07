<?php

namespace App\Filament\Resources\Jawatans;

use App\Filament\Resources\Jawatans\Pages\CreateJawatan;
use App\Filament\Resources\Jawatans\Pages\EditJawatan;
use App\Filament\Resources\Jawatans\Pages\ListJawatans;
use App\Filament\Resources\Jawatans\Schemas\JawatanForm;
use App\Filament\Resources\Jawatans\Tables\JawatansTable;
use App\Models\Jawatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;


class JawatanResource extends Resource
{
    protected static ?string $model = Jawatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'desc_jawatan';

    protected static ?string $navigationLabel = 'Jawatan & Gred';

    protected static ?string $modelLabel = 'Jawatan & Gred';

    protected static ?string $pluralModelLabel = 'Jawatan & Gred';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';
        protected static ?int $navigationSort = 27;


    public static function form(Schema $schema): Schema
    {
        return JawatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JawatansTable::configure($table);
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
            'index' => ListJawatans::route('/'),
            'create' => CreateJawatan::route('/create'),
            'edit' => EditJawatan::route('/{record}/edit'),
        ];
    }
// public static function getEloquentQuery(): Builder
// {
//     return parent::getEloquentQuery()
//         ->with('greds')
//         ->select('jawatans.*');
// }
}
