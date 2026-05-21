<?php

namespace App\Filament\Resources\OpsyenPencens;

use App\Filament\Resources\OpsyenPencens\Pages\CreateOpsyenPencen;
use App\Filament\Resources\OpsyenPencens\Pages\EditOpsyenPencen;
use App\Filament\Resources\OpsyenPencens\Pages\ListOpsyenPencens;
use App\Filament\Resources\OpsyenPencens\Schemas\OpsyenPencenForm;
use App\Filament\Resources\OpsyenPencens\Tables\OpsyenPencensTable;
use App\Models\OpsyenPencen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpsyenPencenResource extends Resource
{
    protected static ?string $model = OpsyenPencen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'opsyen';
    protected static ?string $modelLabel = 'Opsyen Pencen';
    protected static ?string $pluralModelLabel = 'Opsyen Pencen';
    protected static ?string $navigationLabel = 'Opsyen Pencen';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    public static function form(Schema $schema): Schema
    {
        return OpsyenPencenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpsyenPencensTable::configure($table);
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
            'index' => ListOpsyenPencens::route('/'),
            // 'create' => CreateOpsyenPencen::route('/create'),
            // 'edit' => EditOpsyenPencen::route('/{record}/edit'),
        ];
    }
}
