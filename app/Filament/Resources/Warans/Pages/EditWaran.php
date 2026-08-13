<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use App\Models\Waran;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditWaran extends EditRecord
{
    protected static string $resource = WaranResource::class;

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

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

    public function getBreadcrumbs(): array
    {
        // The back button (see getHeading()) replaces the need for a
        // breadcrumb trail on this page.
        return [];
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString(
            '<button type="button" onclick="window.history.back()" class="mystaff-back-btn" aria-label="Kembali">' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' .
            '</button>' .
            '<span>' . e($this->getTitle()) . '</span>'
        );
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

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }


}
