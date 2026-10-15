@php
    $statusLabel = match ($record->status) {
        'removed' => 'Dibuang',
        'pindaan nama' => 'Pindaan Nama',
        'batal nama' => 'Batal Nama',
        default => 'Aktif',
    };

    $statusStyle = match ($record->status) {
        'removed' => 'background-color:#fee2e2;color:#b91c1c;',
        'pindaan nama' => 'background-color:#dbeafe;color:#1d4ed8;',
        'batal nama' => 'background-color:#fef3c7;color:#b45309;',
        default => 'background-color:#dcfce7;color:#15803d;',
    };

    $aktiviti = $record->aktiviti
        ? trim(($record->aktiviti->no_aktivit ?? '').' - '.($record->aktiviti->nama_aktiviti ?? ''), ' -')
        : '-';

    $jawatanGred = collect([
        filled($record->jawatan_list) ? $record->jawatan_list : null,
        filled($record->gred_list) ? 'GRED '.$record->gred_list : null,
    ])->filter()->implode(' , ') ?: '-';

    $showBahagian = (bool) $record->ptj?->usesBahagianHierarchy();

    $rows = [
        ['icon' => 'heroicon-o-document-text', 'iconClass' => 'text-fg-indigo', 'label' => 'No Waran', 'value' => $record->waran?->no_waran ?: '-'],
        ['icon' => 'heroicon-o-clipboard-document-list', 'iconClass' => 'text-fg-pink', 'label' => 'Butiran', 'value' => $record->butiran ?: '-'],
        ['icon' => 'heroicon-o-calendar-days', 'iconClass' => 'text-fg-brand', 'label' => 'Tarikh Kuatkuasa Waran', 'value' => $record->tarikh_kuatkuasa ? \Illuminate\Support\Carbon::parse($record->tarikh_kuatkuasa)->translatedFormat('d F Y') : 'Tiada'],
        ['icon' => 'heroicon-o-bolt', 'iconClass' => 'text-fg-warning-subtle', 'label' => 'Aktiviti', 'value' => $aktiviti],
        ['icon' => 'heroicon-o-briefcase', 'iconClass' => 'text-fg-purple', 'label' => 'Jawatan / Gred', 'value' => $jawatanGred],
        ['icon' => 'heroicon-o-building-office-2', 'iconClass' => 'text-fg-cyan', 'label' => 'PTJ', 'value' => $record->ptj?->nama_ptj ?: '-'],
    ];

    if ($showBahagian) {
        $rows[] = ['icon' => 'heroicon-o-building-office', 'iconClass' => 'text-fg-cyan', 'label' => 'Bahagian', 'value' => $record->bahagian?->nama_bahagian ?: 'Tiada'];
    }

    $rows[] = ['icon' => 'heroicon-o-squares-2x2', 'iconClass' => 'text-fg-yellow', 'label' => 'Jabatan / KK / KP', 'value' => $record->unit?->nama_unit ?: 'Tiada'];
    $rows[] = ['icon' => 'heroicon-o-square-2-stack', 'iconClass' => 'text-fg-brand', 'label' => 'KD / KKIA / Wad / Klinik', 'value' => $record->subunit?->nama_subunit ?: 'Tiada'];
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
    <table class="w-full border-collapse text-sm">
        <tbody>
            @foreach($rows as $index => $row)
                <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-transparent' : 'bg-gray-50/70 dark:bg-white/[0.02]' }}">
                    <th class="w-1/3 border border-gray-200 px-3 py-2 text-left font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <span class="inline-flex items-center gap-2">
                            <x-filament::icon :icon="$row['icon']" @class(['w-4 h-4', $row['iconClass']]) />
                            {{ $row['label'] }}
                        </span>
                    </th>
                    <td class="border border-gray-200 px-3 py-2 text-gray-950 dark:border-white/10 dark:text-white">
                        {{ $row['value'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>