<?php

namespace App\Filament\Resources\Pegawais\Tables;

use App\Filament\Resources\Pegawais\PegawaiResource;
use App\Models\Pegawai;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->recordAction(null)
            ->defaultPaginationPageOption(5)
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(null)
            ->recordClasses(fn (Pegawai $record) => static::lantikanSlug($record)
                ? 'fi-ta-row-'.static::lantikanSlug($record)
                : null)
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex()
                    ->width(1),
                TextColumn::make('nama')
                    ->label('Pegawai')
                    ->formatStateUsing(function (Pegawai $record): string {
                        $tidakLengkap = $record->isTidakLengkap();
                        $statusLabel = $tidakLengkap ? 'Tidak Lengkap' : 'Lengkap';
                        $statusClasses = $tidakLengkap
                            ? 'bg-danger-soft text-fg-danger-strong'
                            : 'bg-success-soft text-fg-success-strong';

                        $tooltip = $tidakLengkap ? $record->tidakLengkapTooltip() : null;
                        $titleAttr = $tooltip !== null
                            ? ' title="'.e($tooltip).'" class="fi-badge inline-flex cursor-help items-center rounded-md px-2 py-0.5 text-xs font-medium '.$statusClasses.'"'
                            : ' class="fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium '.$statusClasses.'"';

                        return '<strong>'.e($record->nama ?? '').'</strong><br> '
                            .'<span class="text-xs text-gray-500">'.e($record->nokp ?? '').'</span><br>'
                            .'<span'.$titleAttr.'>'
                            .e($statusLabel)
                            .'</span><br>';
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $searchLower = strtolower($search);

                        $query->where(function ($q) use ($search, $searchLower): void {
                            $q->where('nama', 'like', "%{$search}%")
                                ->orWhere('nokp', 'like', "%{$search}%")
                                ->orWhereHas('jawatan_gred.jawatan', function ($jawatan) use ($search): void {
                                    $jawatan->where('desc_jawatan', 'like', "%{$search}%");
                                })
                                ->orWhereHas('jawatan_gred.gred', function ($gred) use ($search): void {
                                    $gred->where('kod_gred', 'like', "%{$search}%");
                                });

                            if (str_contains($searchLower, 'tidak lengkap')) {
                                $q->orWhere(fn ($status) => $status->tidakLengkap());
                            } elseif (str_contains($searchLower, 'lengkap')) {
                                $q->orWhere(fn ($status) => $status->lengkap());
                            }
                        });
                    })
                    ->sortable(
                        query: function ($query, string $direction): void {
                            $query->orderByRaw("
                                                    CASE
                                                        WHEN ptj_id IS NULL
                                                            OR (
                                                                bahagian_id IS NULL
                                                                AND EXISTS (
                                                                    SELECT 1 FROM ptjs
                                                                    WHERE ptjs.id = pegawais.ptj_id
                                                                      AND ptjs.is_jkn = 1
                                                                )
                                                            )
                                                            OR (unit_id IS NULL AND ada_unit = 0)
                                                            OR (subunit_id IS NULL AND ada_subunit = 0)
                                                            OR (
                                                                is_kontrak = 0
                                                                AND is_jtw = 0
                                                                AND NOT EXISTS (
                                                                    SELECT 1
                                                                    FROM waran_jawatans
                                                                    INNER JOIN warans ON warans.id = waran_jawatans.waran_id
                                                                    WHERE waran_jawatans.pegawai_id = pegawais.id
                                                                      AND waran_jawatans.deleted_at IS NULL
                                                                )
                                                            )
                                                        THEN 0
                                                        ELSE 1
                                                    END {$direction}
                                                ");
                        }
                    ),

                TextColumn::make('ptj')
                    ->label('PTJ')
                    ->formatStateUsing(function ($record) {

                        $html =
                            '<strong>'.e($record->ptj?->nama_ptj ?? '').'</strong><br>'.
                            '<span class="block whitespace-normal break-words text-xs text-gray-500">'.e($record->jawatan_gred
                                ? $record->jawatan_gred->jawatan->desc_jawatan.' ('.$record->jawatan_gred->gred->kod_gred.')'
                                : '').'</span>';
                        // '<span class="text-xs text-gray-500">'.($record->ptj?->programs?->pluck('nama_program')->filter()->implode(', ') ?? '').'</span>';

                        $waranJawatan = $record->waranJawatan;

                        $ptj_pegawai = $record->ptj?->id;
                        $ptj_waran = $record->waranJawatan?->ptj?->id;

                        if ($waranJawatan && ! $record->is_kontrak && $ptj_pegawai !== $ptj_waran) {
                            $html .= '<br><span class="text-xs px-2 py-1 rounded bg-warning-100 text-warning-700">
        Pinjam
    </span>';
                        }

                        return $html;
                    })

                    ->html()
                    ->wrap()
                    ->sortable(
                        query: function ($query, string $direction) {
                            $query
                                ->leftJoin('ptjs', 'pegawais.ptj_id', '=', 'ptjs.id')
                                ->orderBy('ptjs.nama_ptj', $direction)
                                ->select('pegawais.*');
                        }
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('ptj', function ($q) use ($search) {
                            $q->where('nama_ptj', 'like', "%{$search}%")
                                ->orWhereHas('programs', function ($p) use ($search) {
                                    $p->where('nama_program', 'like', "%{$search}%");
                                });
                        });
                    }),

                TextColumn::make('waran')
                    ->label('Waran')
                    ->getStateUsing(function ($record) {

                        if ($record->is_jtw == 1) {
                            return '<strong>Jawatan tanpa waran</strong>';
                        }

                        return '<strong>'.($record->waranJawatan?->waran?->no_waran ?? '').'</strong>';
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('waranJawatan.waran', function ($q) use ($search) {
                            $q->where('no_waran', 'like', "%{$search}%");
                        });

                        if (str_contains(strtolower('jawatan tanpa waran'), strtolower($search))) {
                            $query->orWhere('is_jtw', 1);
                        }
                    })
                    ->sortable(
                        query: function ($query, string $direction) {
                            $query
                                ->leftJoin('waran_jawatans', 'pegawais.id', '=', 'waran_jawatans.pegawai_id')
                                ->leftJoin('warans', 'waran_jawatans.waran_id', '=', 'warans.id')
                                ->orderBy('warans.no_waran', $direction)
                                ->select('pegawais.*');
                        }
                    ),
            ])
            ->filters([
                SelectFilter::make('ptj_id')
                    ->label('PTJ')
                    ->relationship('ptj', 'nama_ptj')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Lengkap' => 'Lengkap',
                        'Tidak Lengkap' => 'Tidak Lengkap',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'Tidak Lengkap') {
                            $query->tidakLengkap();
                        }

                        if ($value === 'Lengkap') {
                            $query->lengkap();
                        }

                        return $query;
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersApplyAction(fn (Action $action) => $action->label('Cari'))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Papar')
                        ->modal()
                        ->modalHeading(fn ($record) => $record->nama)
                        ->extraModalWindowAttributes(fn (Pegawai $record) => [
                            'class' => static::lantikanSlug($record)
                                ? 'fi-modal-window-'.static::lantikanSlug($record)
                                : null,
                        ])
                        ->extraModalFooterActions([
                            Action::make('edit')
                                ->label('Kemaskini')
                                ->url(fn ($record) => PegawaiResource::getUrl('edit', [
                                    'record' => $record,
                                ])),
                        ]),

                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn ($record) => "Padam {$record->nama}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->after(function ($record) {

                            Log::info('Pegawai Deleted', [
                                'pegawai_id' => $record->id,
                                'user_id' => auth()->id(),
                            ]);

                            $creator = auth()->user();

                            $recipients = User::whereIn('role', [1, 2])->get();

                            Notification::make()
                                ->title('Pegawai Telah Dipadam')
                                ->body("Pegawai {$record->nama} telah dipadam oleh {$creator->name}")
                                ->danger()
                                ->sendToDatabase($recipients);
                        }),
                ]),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function lantikanSlug(Pegawai $record): ?string
    {
        return match (true) {
            $record->is_tetap == 1 => 'tetap',
            $record->is_kontrak_interim == 1 => 'kontrak-interim',
            $record->is_kontrak_isi_tetap == 1 => 'kontrak-isi-tetap',
            $record->is_kontrak == 1 => 'kontrak',
            default => null,
        };
    }
}
