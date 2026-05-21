@php
    $waran = $getRecord();
    $i = 0;
@endphp

<div class="overflow-x-auto rounded-xl border border-gray-200">
<button wire:click="test">
    Test
</button>    <table class="w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left">Bil</th>
                <th class="px-4 py-3 text-left">Butiran</th>
                <th class="px-4 py-3 text-left">Aktiviti</th>
                <th class="px-4 py-3 text-left">PTJ</th>
                <th class="px-4 py-3 text-left">Gred</th>
                <th class="px-4 py-3 text-left">Jawatan</th>
                <th class="px-4 py-3 text-left">Nama Penyandang</th>
                <th class="px-4 py-3 text-left">Tindakan</th>

            </tr>
        </thead>

        <tbody>
            @forelse ($waran->waranJawatan as $item)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ ++$i }}</td>

                    <td class="px-4 py-3 font-semibold">
                        {{ $item->butiran ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $item->aktiviti?->no_aktivit . ' - ' . $item->aktiviti?->nama_aktiviti ?? '-' }}
                    </td>


                    <td class="px-4 py-3">
                        {{ $item->ptj->nama_ptj ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $item->jawatan->desc_jawatan ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        @foreach ($item->gred_ids ?? [] as $id)
                            <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs">
                                {{ \App\Models\Gred::find($id)?->kod_gred }}
                            </span>
                        @endforeach
                    </td>

                    <td class="px-4 py-3">
                        {{ $item->pegawai->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">

                            {{-- <a href="#"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">

                                <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4" />

                                Edit
                            </a> --}}
                            <x-filament::link
    color="primary"
    icon="heroicon-o-pencil"
    wire:click="$dispatch('open-action-modal', {
        name: 'editJawatan',
        arguments: { id: {{ $item->id }} }
    })"
>
    Edit
</x-filament::link>
                            <a href="#"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">

                                <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />

                                Delete
                            </a>

                        </div>
                    </td>


                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                        Tiada data jawatan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
