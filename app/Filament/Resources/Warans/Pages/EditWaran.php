<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use App\Models\Waran;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditWaran extends EditRecord
{
    protected static string $resource = WaranResource::class;



    protected function getListeners(): array
    {
        return [
            'setWaran' => 'setWaran',
        ];
    }

    public function setWaran($id): void
    {
        $waran = Waran::with('waranJawatans')->find($id);

        $this->form->fill([
            'selected_waran_id' => $id,
            'catatan' => $waran?->catatan,
            'waranJawatans' => $waran?->waranJawatans->toArray(),
        ]);
    }
    protected function getHeaderActions(): array
    {

        return [
            // Action::make('createNewVersion')
            //     ->label('Tambah Waran Baru')
            //     ->form([
            //         TextInput::make('no_waran')
            //             ->required(),

            //         TextInput::make('jik')
            //             ->numeric()
            //             ->required(),
            //     ])
            //     ->action(function (array $data, $record) {

            //         // 1. create new waran
            //         $new = Waran::create([
            //             'no_waran' => $data['no_waran'],
            //             'jik' => $data['jik'],
            //             'parent_id' => $record->id,
            //         ]);

            //         // 2. create repeater rows based on jik
            //         for ($i = 0; $i < (int) $data['jik']; $i++) {
            //             $new->waranJawatan()->create([]);
            //         }

            //         // 3. redirect
            //         return redirect(
            //             WaranResource::getUrl('edit', [
            //                 'record' => $new->id,
            //             ])
            //         );
            //     }),

            // Action::make('tambahWaran')
            // ->label('Tambah Jawatan')
            // ->icon('heroicon-o-plus')
            // ->form([
            //     TextInput::make('no_waran')
            //         ->label('No Waran')
            //         ->required(),

            //     TextInput::make('jik')
            //         ->label('Jumlah Jawatan')
            //         ->numeric()
            //         ->required(),
            // ])
            // ->action(function (array $data) {

            //     $parent = $this->record;

            //     Waran::create([
            //         'no_waran' => $data['no_waran'],
            //         'jik' => $data['jik'],
            //         'parent_id' => $parent->id,
            //     ]);

            //     // refresh page so repeater/relations update
            //     $this->dispatch('refresh');
            // }),

            // DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Kemaskini Waran');
    }



}
