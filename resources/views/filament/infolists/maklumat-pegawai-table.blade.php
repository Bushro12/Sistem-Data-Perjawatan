<table class="w-full border-collapse text-sm">
    <tbody>
        <tr>
            <th class="w-1/3 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-fg-brand" />
                    Nama
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2 font-bold">
                {{ $record->nama }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-identification" class="w-4 h-4 text-fg-indigo" />
                    No kad pengenalan
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $record->nokp }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-user-circle" class="w-4 h-4 text-fg-pink" />
                    Jantina
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $record->jantina }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-briefcase" class="w-4 h-4 text-fg-purple" />
                    Jawatan
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $record->jawatan_gred?->jawatan?->desc_jawatan }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-star" class="w-4 h-4 text-fg-warning-subtle" />
                    Gred
                </span>
            </th>
            <td class="border border-gray-200 dark:border-white/10 px-3 py-2">
                {{ $record->jawatan_gred?->gred?->kod_gred }}
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
                {{ $record->ptj?->nama_ptj }}
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
                {{ $record->bahagian?->nama_bahagian }}
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
                {{ $record->unit?->nama_unit }}
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
                {{ $record->subunit?->nama_subunit }}
            </td>
        </tr>
    </tbody>
</table>
