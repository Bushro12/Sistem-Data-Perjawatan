<?php

namespace App\Filament\Resources\WaranJawatans;

use App\Filament\Resources\WaranJawatans\Pages\CreateWaranJawatan;
use App\Filament\Resources\WaranJawatans\Pages\EditWaranJawatan;
use App\Filament\Resources\WaranJawatans\Pages\ListWaranJawatans;
use App\Filament\Resources\WaranJawatans\Pages\ViewWaranJawatan;
use App\Filament\Resources\WaranJawatans\Schemas\WaranJawatanForm;
use App\Filament\Resources\WaranJawatans\Schemas\WaranJawatanInfolist;
use App\Filament\Resources\WaranJawatans\Tables\WaranJawatansTable;
use App\Models\WaranJawatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaranJawatanResource extends Resource
{
    protected static ?string $model = WaranJawatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'butiran';

    protected static ?string $modelLabel = 'Nama Penyandang';

    protected static ?string $pluralModelLabel = 'Nama Penyandang';

protected static ?string $navigationLabel = 'Nama Penyandang';

    protected static string|\UnitEnum|null $navigationGroup = 'Buku Waran';

        protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return WaranJawatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WaranJawatanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaranJawatansTable::configure($table);
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
            'index' => ListWaranJawatans::route('/'),
            'create' => CreateWaranJawatan::route('/create'),
            'view' => ViewWaranJawatan::route('/{record}'),
            'edit' => EditWaranJawatan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
