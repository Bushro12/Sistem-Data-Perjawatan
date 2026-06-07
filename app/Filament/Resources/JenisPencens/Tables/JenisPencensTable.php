<?php

<<<<<<<< HEAD:app/Filament/Resources/Kumpulans/Tables/KumpulansTable.php
namespace App\Filament\Resources\Kumpulans\Tables;
========
namespace App\Filament\Resources\JenisPencens\Tables;
>>>>>>>> 938c0d655df628827656ae88289b79ef503af684:app/Filament/Resources/JenisPencens/Tables/JenisPencensTable.php

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

<<<<<<<< HEAD:app/Filament/Resources/Kumpulans/Tables/KumpulansTable.php
class KumpulansTable
========
class JenisPencensTable
>>>>>>>> 938c0d655df628827656ae88289b79ef503af684:app/Filament/Resources/JenisPencens/Tables/JenisPencensTable.php
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                ->Label('Bil')
                ->rowIndex(),
<<<<<<<< HEAD:app/Filament/Resources/Kumpulans/Tables/KumpulansTable.php
                TextColumn::make('nama_kumpulan')
                ->label('Kumpulan')
                ->sortable()
                ->searchable()
========
                TextColumn::make('jenis')
                ->label('Jenis Pencen')
                ->sortable()
                ->searchable(),
                TextColumn::make('kategori')
>>>>>>>> 938c0d655df628827656ae88289b79ef503af684:app/Filament/Resources/JenisPencens/Tables/JenisPencensTable.php
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                ->modal(),
<<<<<<<< HEAD:app/Filament/Resources/Kumpulans/Tables/KumpulansTable.php
                DeleteAction::make()
========
                DeleteAction::make(),
>>>>>>>> 938c0d655df628827656ae88289b79ef503af684:app/Filament/Resources/JenisPencens/Tables/JenisPencensTable.php
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
