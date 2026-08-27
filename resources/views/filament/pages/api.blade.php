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
        </div>
    </x-filament::section>
</x-filament-panels::page>
