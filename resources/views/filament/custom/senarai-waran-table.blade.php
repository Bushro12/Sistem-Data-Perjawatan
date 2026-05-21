@php $i = 0; @endphp


<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
    <div class="flex items-center justify-between flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 p-4">
        <div>
        </div>
        <label for="input-group-1" class="sr-only">Search</label>
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                        d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input type="text" id="input-group-1"
                class="block w-full max-w-96 ps-9 pe-3 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                placeholder="Search">
        </div>
    </div>
    <table class="w-full text-sm text-left rtl:text-right text-body">
        <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-t border-default-medium">
            <tr>

                <th scope="col" class="px-6 py-3 font-medium">
                    Bil
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Waran
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Butiran
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Aktiviti
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    PTJ
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Status
                </th>
                <th scope="col" class="px-6 py-3 font-medium">
                    Tindakan
                </th>

            </tr>
        </thead>
        <tbody>
            @foreach ($warans as $waran)
                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">

                    <td class="px-6 py-4">
                        {{ ++$i }}
                    </td>
                    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                        {{ $waran->no_waran }}
                    </th>
                    <td class="px-6 py-4">
                        <ul class="list-disc pl-5">
                            @foreach ($waran->waranJawatans as $jawatan)
                                {{ $jawatan->butiran }}
                            @endforeach
                        </ul>
                    </td>
                    <td class="px-6 py-4">
                        <ul class="list-disc pl-5">
                            @foreach ($waran->waranJawatans as $jawatan)
                                {{ $jawatan->aktiviti?->no_aktivit }} - {{ $jawatan->aktiviti?->nama_aktiviti }}
                            @endforeach
                        </ul>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $grouped = $waran->waranJawatans->groupBy(fn($j) => $j->ptj?->nama_ptj);
                        @endphp

                        @foreach ($grouped as $ptjName => $items)
                            <div class="mb-2">
                                <div class="font-semibold">
                                    {{ $ptjName ?? 'PTJ Tidak Diketahui' }}
                                </div>

                                <ol class="list-decimal pl-6">
                                    @foreach ($items as $jawatan)
                                        <li>
                                            {{ $jawatan->pegawai?->nama ?? 'Kosong' }}
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $total = $waran->waranJawatans->count();
                            $filled = $waran->waranJawatans->whereNotNull('pegawai_id')->count();
                        @endphp

                        @if ($total > 0 && $filled === $total)
                            <span
                                class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-success-subtle text-fg-success-strong text-sm font-medium rounded bg-success-soft">Seimbang</span>
                        @elseif ($total > 0 && $filled > $total)
                            <span
                                class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-danger-subtle text-fg-danger-strong text-sm font-medium rounded bg-danger-soft">Lebih</span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-warning-subtle text-fg-warning text-sm font-medium rounded bg-warning-soft">Kurang</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <button type="button" onclick="Livewire.dispatch('setWaran', { id: {{ $waran->id }} })"
                            class="text-fg-brand hover:underline">
                            Edit
                        </button>
                    </td>
                </tr>

                @foreach ($waran->children as $child)
                    <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                        <td class="px-6 py-4">
                            {{ ++$i }}
                        </td>
                        <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                            ↳ {{ $child->no_waran }}
                        </th>
                        <td class="px-6 py-4">
                            <ul class="list-disc pl-5">
                                @foreach ($child->waranJawatans as $jawatan)
                                    {{ $jawatan->butiran }}
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-6 py-4">
                            <ul class="list-disc pl-5">
                                @foreach ($child->waranJawatans as $jawatan)
                                    {{ $jawatan->aktiviti?->no_aktivit }} - {{ $jawatan->aktiviti?->nama_aktiviti }}
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $grouped = $child->waranJawatans->groupBy(fn($j) => $j->ptj?->nama_ptj);
                            @endphp

                            @foreach ($grouped as $ptjName => $items)
                                <div class="mb-2">
                                    <div class="font-semibold">
                                        {{ $ptjName ?? 'PTJ Tidak Diketahui' }}
                                    </div>

                                    <ol class="list-decimal pl-6">
                                        @foreach ($items as $jawatan)
                                            <li>
                                                {{ $jawatan->pegawai?->nama ?? 'Kosong' }}
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $total = $child->waranJawatans->count();
                                $filled = $child->waranJawatans->whereNotNull('pegawai_id')->count();
                            @endphp

                            @if ($total > 0 && $filled === $total)
                                <span
                                    class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-success-subtle text-fg-success-strong text-sm font-medium rounded bg-success-soft">Seimbang</span>
                            @elseif ($total > 0 && $filled > $total)
                                <span
                                    class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-danger-subtle text-fg-danger-strong text-sm font-medium rounded bg-danger-soft">Lebih</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 ring-1 ring-inset ring-warning-subtle text-fg-warning text-sm font-medium rounded bg-warning-soft">Kurang</span>
                            @endif

                        <td class="px-6 py-4">
                            <button type="button" onclick="Livewire.dispatch('setWaran', { id: {{ $child->id }} })"
                                class="text-fg-brand hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            @endforeach

        </tbody>
    </table>
</div>
