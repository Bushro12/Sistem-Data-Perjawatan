<x-filament-panels::page>
    <x-filament::section heading="API">
        <p class="fi-in-text">
            Halaman API. Masukkan No. KP untuk mendapatkan Nama Pegawai daripada API luaran (<code>api/document.md</code>) menggunakan kunci dalam <code>api/credentials.text</code>.
        </p>
    </x-filament::section>

    <x-filament::section heading="Carian Pegawai">
        <div class="space-y-4">
            <div>
                <label for="nokp" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">No. KP</span>
                </label>
                <input
                    id="nokp"
                    type="text"
                    wire:model.live.debounce.500ms="nokp"
                    placeholder="Masukkan No. KP tanpa sengkang (contoh: 950817025018)"
                    maxlength="12"
                    inputmode="numeric"
                    class="fi-input mt-1 block w-full rounded-lg border-none bg-white px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:ring-white/20 dark:focus:ring-primary-500"
                />
                <p class="fi-fo-hint mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan 12 digit No. KP pegawai. Contoh: <code>950817025018</code></p>
            </div>

            <div>
                <label for="nama-pegawai" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Nama Pegawai</span>
                    <span wire:loading wire:target="nokp" class="text-xs text-gray-500">Memuat...</span>
                </label>
                <input
                    id="nama-pegawai"
                    type="text"
                    wire:model="namaPegawai"
                    disabled
                    placeholder="Nama akan dipaparkan automatik berdasarkan No. KP (via API)"
                    class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                />
                <p class="fi-fo-hint mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($this->apiError)
                        <span class="text-danger-600 dark:text-danger-400">{{ $this->apiError }}</span>
                    @elseif ($this->nokp && $this->namaPegawai)
                        Ditemui melalui API / pangkalan data tempatan.
                    @elseif ($this->nokp && strlen($this->nokp) < 12)
                        Sila lengkapkan 12 digit No. KP.
                    @else
                        Nama pegawai akan dipaparkan setelah No. KP yang sah dimasukkan.
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="jantina-nama" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Jantina</span>
                    </label>
                    <input
                        id="jantina-nama"
                        type="text"
                        wire:model="jantinaNama"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">jantina → nama</p>
                </div>

                <div>
                    <label for="gred-kod" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Gred</span>
                    </label>
                    <input
                        id="gred-kod"
                        type="text"
                        wire:model="gredKod"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">gred → kod</p>
                </div>

                <div>
                    <label for="ptj-nama" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">PTJ</span>
                    </label>
                    <input
                        id="ptj-nama"
                        type="text"
                        wire:model="ptjNama"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">ptj → nama</p>
                </div>

                <div>
                    <label for="bahagian-nama" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Bahagian</span>
                    </label>
                    <input
                        id="bahagian-nama"
                        type="text"
                        wire:model="bahagianNama"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">bahagian → nama</p>
                </div>

                <div>
                    <label for="unit-nama" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Unit</span>
                    </label>
                    <input
                        id="unit-nama"
                        type="text"
                        wire:model="unitNama"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">unit → nama</p>
                </div>

                <div>
                    <label for="jawatan-nama" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Jawatan</span>
                    </label>
                    <input
                        id="jawatan-nama"
                        type="text"
                        wire:model="jawatanNama"
                        disabled
                        placeholder="—"
                        class="fi-input mt-1 block w-full rounded-lg border-none bg-gray-50 px-3 py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 placeholder:text-gray-400 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="fi-fo-hint mt-1 text-xs text-gray-500 dark:text-gray-400">jawatan → nama</p>
                </div>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Senarai PTJ (via API)">
        <div class="space-y-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Dapatkan senarai penuh PTJ daripada API luaran (<code>GET /pegawai</code> — medan <code>ptj</code> diagregat &amp; dinyahduplikasi). API tidak menyediakan endpoint <code>/ptj</code> khusus, jadi data PTJ dikumpul dengan menelusuri semua halaman pegawai.
            </p>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="fetchAllPtj"
                    wire:loading.attr="disabled"
                    wire:target="fetchAllPtj"
                    class="fi-btn fi-btn-color-primary fi-color-primary inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-600 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="fetchAllPtj">Fetch All PTJ</span>
                    <span wire:loading wire:target="fetchAllPtj">Memuat...</span>
                </button>

                <span wire:loading wire:target="fetchAllPtj" class="text-sm text-gray-500">Menarik data PTJ daripada API...</span>

                @if ($this->ptjs !== null && $this->ptjError === null)
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ count($this->ptjs) }} PTJ dijumpai</span>
                @endif
            </div>

            @if ($this->ptjError)
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $this->ptjError }}</p>
            @endif

            @if ($this->ptjs !== null && empty($this->ptjs) && $this->ptjError === null)
                <p class="text-sm text-gray-500 dark:text-gray-400">Tiada PTJ dijumpai.</p>
            @endif

            @if ($this->ptjs !== null && ! empty($this->ptjs))
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <div class="max-h-[480px] overflow-auto">
                        <table class="w-full table-auto text-sm">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">#</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Kod PTJ</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Nama PTJ</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">ID</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach ($this->ptjs as $index => $ptj)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2 font-mono text-gray-700 dark:text-gray-200">{{ $ptj['kod'] ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $ptj['nama'] ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $ptj['id'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
