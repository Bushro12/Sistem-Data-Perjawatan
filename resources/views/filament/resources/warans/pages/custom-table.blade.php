<x-filament-panels::page>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Jumlah Jawatan</p>
            <h2 class="text-2xl font-bold">100</h2>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Jawatan Diisi</p>
            <h2 class="text-2xl font-bold">90</h2>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Jumlah Kekosongan</p>
            <h2 class="text-2xl font-bold">10</h2>
        </div>

    </div>

    {{-- TABS --}}
    <div class="flex gap-2 mb-4 overflow-x-auto">
        @foreach ([1,2,3,4,5,7] as $tab)
            <button class="px-4 py-2 rounded-lg text-sm
                {{ $tab == 1 ? 'bg-primary-600 text-white' : 'bg-gray-100' }}">
                Program {{ $tab }}
            </button>
        @endforeach
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">

        {{-- small header label --}}
        <div class="p-3 border-b text-sm text-gray-500">
            1.1.7.1
        </div>

        {{-- search row --}}
        <div class="p-3 flex justify-between items-center border-b">
            <input type="text"
                   placeholder="Search"
                   class="border rounded-lg px-3 py-1 w-64">

            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">Filter</span>
            </div>
        </div>

        {{-- table --}}
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Butiran</th>
                    <th class="p-3">Nama Jawatan / Gred Gaji</th>
                    <th class="p-3">Jumlah Jawatan</th>
                    <th class="p-3">Waran</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>

            <tbody>
                <tr class="border-t">
                    <td class="p-3">120</td>
                    <td class="p-3">PEGAWAI TEKNOLOGI MAKLUMAT, F9</td>
                    <td class="p-3">1</td>
                    <td class="p-3">WP 2025/12</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-600">
                            Lebih
                        </span>
                    </td>
                </tr>

                <tr class="border-t">
                    <td class="p-3">121</td>
                    <td class="p-3">PEGAWAI PSIKOLOGI, S12</td>
                    <td class="p-3">1</td>
                    <td class="p-3">WP 2025/12</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-600">
                            Kurang
                        </span>
                    </td>
                </tr>

                <tr class="border-t">
                    <td class="p-3">122</td>
                    <td class="p-3">PEGAWAI PSIKOLOGI, S10</td>
                    <td class="p-3">2</td>
                    <td class="p-3">WP 2025/13</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-600">
                            Seimbang
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- pagination --}}
        <div class="p-3 flex justify-end">
            <button class="px-3 py-1 border rounded">Next</button>
        </div>

    </div>

</x-filament-panels::page>
