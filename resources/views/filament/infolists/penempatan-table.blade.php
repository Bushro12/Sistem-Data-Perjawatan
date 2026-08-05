@php
    $isKontrak = $record->is_kontrak == 1;
    $waranJawatan = $record->waranJawatan;

    $program = $isKontrak
        ? $record->pegawaiKontrak?->program
        : $waranJawatan?->aktiviti?->program;

    $aktiviti = $isKontrak
        ? $record->pegawaiKontrak?->aktiviti
        : $waranJawatan?->aktiviti;

    $ptj = $isKontrak
        ? $record->ptj?->nama_ptj
        : $waranJawatan?->ptj?->nama_ptj;

    $bahagian = $isKontrak
        ? $record->bahagian?->nama_bahagian
        : $waranJawatan?->bahagian?->nama_bahagian;

    $unit = $isKontrak
        ? $record->unit?->nama_unit
        : $waranJawatan?->unit?->nama_unit;

    $subunit = $isKontrak
        ? $record->subunit?->nama_subunit
        : $waranJawatan?->subunit?->nama_subunit;

   $ptjPegawaiId = $record->ptj?->id;
$ptjWaranId = $waranJawatan?->ptj?->id;

$statusPinjam = ($waranJawatan && !$isKontrak && $ptjPegawaiId !== $ptjWaranId)
    ? 'Pinjam'
    : 'Tiada';
@endphp

<table class="w-full border-collapse text-sm">
    <tbody>
        @unless($isKontrak)
            <tr>
                <th class="w-1/3 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4 text-fg-indigo" />
                        No Waran
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ $waranJawatan?->waran?->no_waran }}
                </td>
            </tr>
            <tr>
                <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-4 h-4 text-fg-pink" />
                        Butiran
                    </span>
                </th>
                <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                    {{ $waranJawatan?->butiran }}
                </td>
            </tr>
        @endunless

        <tr>
            <th class="w-1/3 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="w-4 h-4 text-fg-purple" />
                    Program
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $program ? "{$program->nama_program} : {$program->desc_program}" : '-' }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-bolt" class="w-4 h-4 text-fg-warning-subtle" />
                    Aktiviti
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $aktiviti ? "{$aktiviti->no_aktivit} - {$aktiviti->nama_aktiviti}" : '-' }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-office-2" class="w-4 h-4 text-fg-cyan" />
                    PTJ
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $ptj }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-office" class="w-4 h-4 text-fg-lime" />
                    Bahagian
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $bahagian }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-squares-2x2" class="w-4 h-4 text-fg-yellow" />
                    Unit
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $unit }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-square-2-stack" class="w-4 h-4 text-fg-brand" />
                    Sub Unit
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $subunit }}
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
                    'fi-badge inline-flex items-center rounded-md px-2.5 py-1 text-sm font-medium',
                    'bg-danger-soft text-fg-danger-strong' => $statusPinjam === 'Pinjam',
                    'bg-success-soft text-fg-success-strong' => $statusPinjam === 'Tiada',
                ])>
                    {{ $statusPinjam }}
                </span>
            </td>
        </tr>
    </tbody>
</table>
