<?php

namespace App\Filament\Resources\Hebahans;

use App\Filament\Resources\Hebahans\Pages\CreateHebahan;
use App\Filament\Resources\Hebahans\Pages\EditHebahan;
use App\Filament\Resources\Hebahans\Pages\ListHebahans;
use App\Filament\Resources\Hebahans\Schemas\HebahanForm;
use App\Filament\Resources\Hebahans\Tables\HebahansTable;
use App\Models\Hebahan;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HebahanResource extends Resource
{
    protected static ?string $model = Hebahan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $recordTitleAttribute = 'tajuk';

    protected static ?string $modelLabel = 'Hebahan';

    protected static ?string $pluralModelLabel = 'Hebahan';

    protected static ?string $navigationLabel = 'Hebahan';

    protected static string|\UnitEnum|null $navigationGroup = 'Hebahan & Info';

    protected static ?int $navigationSort = 16;

    public static function form(Schema $schema): Schema
    {
        return HebahanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HebahansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Regular users (role 3) only ever see published, non-expired hebahan.
     * Admins and superadmins manage every entry, including drafts.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->isUser()) {
            $query
                ->where('status', 'published')
                ->where(function (Builder $query) {
                    $query
                        ->whereNull('dipaparkan_sehingga')
                        ->orWhere('dipaparkan_sehingga', '>=', now()->toDateString());
                });
        }

        return $query;
    }

    // Only admins/superadmins may create, edit, or delete hebahan — role 3
    // gets a read-only list (enforced above via getEloquentQuery()).
    public static function canCreate(): bool
    {
        return static::isManagedByCurrentUser();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isManagedByCurrentUser();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isManagedByCurrentUser();
    }

    public static function canDeleteAny(): bool
    {
        return static::isManagedByCurrentUser();
    }

    protected static function isManagedByCurrentUser(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdmin() || $user?->isAdmin();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHebahans::route('/'),
            'create' => CreateHebahan::route('/create'),
            'edit' => EditHebahan::route('/{record}/edit'),
        ];
    }
}
