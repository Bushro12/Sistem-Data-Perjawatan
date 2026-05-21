<?php

namespace App\Filament\Resources\Kumpulans;

use App\Filament\Resources\Kumpulans\Pages\CreateKumpulan;
use App\Filament\Resources\Kumpulans\Pages\EditKumpulan;
use App\Filament\Resources\Kumpulans\Pages\ListKumpulans;
use App\Filament\Resources\Kumpulans\Schemas\KumpulanForm;
use App\Filament\Resources\Kumpulans\Tables\KumpulansTable;
use App\Models\Kumpulan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KumpulanResource extends Resource
{
    protected static ?string $model = Kumpulan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_kumpulan';

    protected static ?string $modelLabel ='Kumpulan';

    protected static ?string $pluralModelLabel ='Kumpulan';
    protected static ?string $navigationLabel ='Kumpulan';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    public static function form(Schema $schema): Schema
    {
        return KumpulanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KumpulansTable::configure($table);
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
            'index' => ListKumpulans::route('/'),
            'create' => CreateKumpulan::route('/create'),
            'edit' => EditKumpulan::route('/{record}/edit'),
        ];
    }
}
