<?php

namespace App\Filament\Resources\JenisPencens;

use App\Filament\Resources\JenisPencens\Pages\CreateJenisPencen;
use App\Filament\Resources\JenisPencens\Pages\EditJenisPencen;
use App\Filament\Resources\JenisPencens\Pages\ListJenisPencens;
use App\Filament\Resources\JenisPencens\Schemas\JenisPencenForm;
use App\Filament\Resources\JenisPencens\Tables\JenisPencensTable;
use App\Models\JenisPencen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JenisPencenResource extends Resource
{
    protected static ?string $model = JenisPencen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'jenis';
    protected static ?string $modelLabel = 'Jenis Pencen';

    protected static ?string $pluralModelLabel = 'Jenis Pencen';

    protected static ?string $navigationLabel = 'Jenis Pencen';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    protected static ?int $navigationSort = 31;


    public static function form(Schema $schema): Schema
    {
        return JenisPencenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JenisPencensTable::configure($table);
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
            'index' => ListJenisPencens::route('/'),
            // 'create' => CreateJenisPencen::route('/create'),
            // 'edit' => EditJenisPencen::route('/{record}/edit'),
        ];
    }
}
