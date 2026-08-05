@php
    $lantikan = match (true) {
        $record->is_tetap == 1 => 'Tetap',
        $record->is_kontrak == 1 => 'Kontrak',
        $record->is_kontrak_interim == 1 => 'Kontrak Interim',
        $record->is_kontrak_isi_tetap == 1 => 'Kontrak Isi Tetap',
        default => null,
    };

    $lainLain = match (true) {
        $record->is_kup == 1 => 'Khas Untuk Penyandang (KUP)',
        $record->is_kupj == 1 => 'Khas Untuk Penyandang Jawatan (KUPJ)',
        $record->is_jtw == 1 => 'Jawatan Tanpa Waran (JTW)',
        default => 'Tiada',
    };

    $showTetapFields = $record->is_tetap == 1 || $record->is_kontrak_interim == 1;
    $showKontrakFields = $record->is_kontrak == 1 || $record->is_kontrak_isi_tetap == 1;
@endphp

<table class="w-full border-collapse text-sm">
    <tbody>
        <tr>
            <th class="w-1/3 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4 text-fg-warning" />
                    Lantikan
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                @if($lantikan)
                    <span @class([
                        'fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                        'bg-success-soft text-fg-success-strong' => $lantikan === 'Tetap',
                        'bg-warning-soft text-fg-warning' => $lantikan === 'Kontrak',
                        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' => $lantikan === 'Kontrak Interim',
                        'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-400' => $lantikan === 'Kontrak Isi Tetap',
                    ])>
                        {{ $lantikan }}
                    </span>
                @endif
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-tag" class="w-4 h-4 text-fg-warning" />
                    Lain-lain
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                <span @class([
                    'fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                    'bg-gray-100 text-gray-600 dark:bg-gray-500/20 dark:text-gray-400' => $lainLain === 'Tiada',
                    'bg-warning-soft text-fg-warning' => $lainLain !== 'Tiada',
                ])>
                    {{ $lainLain }}
                </span>
            </td>
        </tr>

        @if($showTetapFields)
            <tr>
                <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-fg-warning" />
                        Tarikh Lantikan
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ \Carbon\Carbon::parse($record->tarikh_lantikan)->format('d-m-Y') }}
                </td>
            </tr>
            <tr>
                <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-fg-warning" />
                        Tarikh Sah Jawatan
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ \Carbon\Carbon::parse($record->tarikh_sah_jawatan)->format('d-m-Y') }}
                </td>
            </tr>
            <tr>
                <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-shield-check" class="w-4 h-4 text-fg-warning" />
                        Opsyen Pencen
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ $record->opsyenPencen?->opsyen }}
                </td>
            </tr>
            <tr>
                <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-fg-warning" />
                        Tarikh Pencen
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ \Carbon\Carbon::parse($record->tarikh_pencen)->format('d-m-Y') }}
                </td>
            </tr>
        @endif

        @if($showKontrakFields)
            @foreach (range(1, 5) as $n)
                @php
                    $tarikhLantikanN = $record->pegawaiKontrak?->{"tarikh_lantikan{$n}"};
                    $tarikhTamatN = $record->pegawaiKontrak?->{"tarikh_tamat{$n}"};
                @endphp
                @if($tarikhLantikanN !== null)
                    <tr>
                        <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-fg-warning" />
                                Tarikh Lantikan {{ $n }}
                            </span>
                        </th>
                        <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                            {{ \Carbon\Carbon::parse($tarikhLantikanN)->format('d-m-Y') }}
                        </td>
                    </tr>
                @endif
                @if($tarikhTamatN !== null)
                    <tr>
                        <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-fg-danger" />
                                Tarikh Tamat {{ $n }}
                            </span>
                        </th>
                        <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                            {{ \Carbon\Carbon::parse($tarikhTamatN)->format('d-m-Y') }}
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
    </tbody>
</table>
