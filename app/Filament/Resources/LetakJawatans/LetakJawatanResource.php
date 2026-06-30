<?php

namespace App\Filament\Resources\LetakJawatans;

use App\Filament\Resources\LetakJawatans\Pages\CreateLetakJawatan;
use App\Filament\Resources\LetakJawatans\Pages\EditLetakJawatan;
use App\Filament\Resources\LetakJawatans\Pages\ListLetakJawatans;
use App\Filament\Resources\LetakJawatans\Pages\ViewLetakJawatan;
use App\Filament\Resources\LetakJawatans\Schemas\LetakJawatanForm;
use App\Filament\Resources\LetakJawatans\Tables\LetakJawatansTable;
use App\Filament\Resources\LetakJawatans\Schemas\LetakJawatanInfolist;

use App\Models\LetakJawatan;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetakJawatanResource extends Resource
{
    protected static ?string $model = LetakJawatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?string $modelLabel = 'Letak Jawatan';

    protected static ?string $navigationLabel = 'Letak Jawatan';
    protected static ?string $pluralModelLabel = 'Letak Jawatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Pegawai';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return LetakJawatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LetakJawatanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetakJawatansTable::configure($table);
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
            'index' => ListLetakJawatans::route('/'),
            'create' => CreateLetakJawatan::route('/create'),
            'view' => ViewLetakJawatan::route('/{record}'),
            'edit' => EditLetakJawatan::route('/{record}/edit'),
        ];
    }
}
