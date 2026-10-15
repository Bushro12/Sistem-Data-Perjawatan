@php
    $pegawai = $record->pegawai;
    $hasPegawai = filled($record->pegawai_id) && $pegawai;
    $jawatanGred = null;

    if ($hasPegawai) {
        $jawatan = $pegawai->jawatan_gred?->jawatan?->desc_jawatan;
        $gred = $pegawai->jawatan_gred?->gred?->kod_gred;
        $tbk = $record->tbk?->tbk;
        $jawatanGred = collect([$jawatan, $gred])->filter()->implode(', ');
        if ($tbk) {
            $jawatanGred .= " (TBK{$tbk})";
        }
    }

    $showPtjPegawai = $hasPegawai && (int) $pegawai->ptj_id !== (int) $record->ptj_id;
@endphp

@if($hasPegawai)
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
        <table class="w-full border-collapse text-sm">
            <tbody>
                <tr class="bg-white dark:bg-transparent">
                    <th class="w-1/3 border border-gray-200 px-3 py-2.5 text-left font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <span class="inline-flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-fg-brand" />
                            Nama Pegawai
                        </span>
                    </th>
                    <td class="border border-gray-200 px-3 py-2.5 font-semibold text-gray-950 dark:border-white/10 dark:text-white">
                        {{ $pegawai->nama }}
                    </td>
                </tr>
                <tr class="bg-gray-50/70 dark:bg-white/[0.02]">
                    <th class="border border-gray-200 px-3 py-2.5 text-left font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <span class="inline-flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-identification" class="w-4 h-4 text-fg-indigo" />
                            No Kad Pengenalan
                        </span>
                    </th>
                    <td class="border border-gray-200 px-3 py-2.5 text-gray-950 dark:border-white/10 dark:text-white">
                        {{ $pegawai->nokp }}
                    </td>
                </tr>
                <tr class="bg-white dark:bg-transparent">
                    <th class="border border-gray-200 px-3 py-2.5 text-left font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <span class="inline-flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-briefcase" class="w-4 h-4 text-fg-purple" />
                            Jawatan / Gred
                        </span>
                    </th>
                    <td class="border border-gray-200 px-3 py-2.5 text-gray-950 dark:border-white/10 dark:text-white">
                        {{ $jawatanGred ?: '-' }}
                    </td>
                </tr>
                @if($showPtjPegawai)
                    <tr class="bg-gray-50/70 dark:bg-white/[0.02]">
                        <th class="border border-gray-200 px-3 py-2.5 text-left font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <span class="inline-flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-building-office-2" class="w-4 h-4 text-fg-cyan" />
                                PTJ Pegawai
                            </span>
                        </th>
                        <td class="border border-gray-200 px-3 py-2.5 text-gray-950 dark:border-white/10 dark:text-white">
                            {{ $pegawai->ptj?->nama_ptj ?: 'Tiada' }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@else
    <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center dark:border-white/15 dark:bg-white/5">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-gray-200 dark:bg-white/10 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-user-minus" class="h-6 w-6 text-gray-400" />
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-950 dark:text-white">Tiada Penyandang</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jawatan waran ini belum diisi oleh mana-mana pegawai.</p>
        </div>
    </div>
@endif