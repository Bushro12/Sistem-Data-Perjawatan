<?php

namespace App\Filament\Resources\Ptjs;

use App\Filament\Resources\Ptjs\Pages\CreatePtj;
use App\Filament\Resources\Ptjs\Pages\EditPtj;
use App\Filament\Resources\Ptjs\Pages\ListPtjs;
use App\Filament\Resources\Ptjs\Schemas\PtjForm;
use App\Filament\Resources\Ptjs\Tables\PtjsTable;
use App\Models\Ptj;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PtjResource extends Resource
{
    protected static ?string $model = Ptj::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_ptj';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';
    protected static ?int $navigationSort = 21;

    protected static ?string $navigationLabel = 'PTJ';
    protected static ?string $pluralModelLabel = 'PTJ';
    Protected static ?string $modelLabel = 'PTJ';

    public static function form(Schema $schema): Schema
    {
        return PtjForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PtjsTable::configure($table);
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
            'index' => ListPtjs::route('/'),
            'create' => CreatePtj::route('/create'),
            'edit' => EditPtj::route('/{record}/edit'),
        ];
    }
}
