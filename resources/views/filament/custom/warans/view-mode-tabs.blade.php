@php
    $isActive = fn($mode) => $this->viewMode === $mode;
@endphp

<div class="sm:hidden">
    <select wire:model="viewMode"
        class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base">
        <option value="active">Aktif</option>
        <option value="inactive">Dibuang</option>
    </select>
</div>

<ul class="hidden sm:flex text-sm font-medium text-center -space-x-px">

    <li class="w-full">
        <button wire:click="$set('viewMode', 'active')" type="button"
            class="w-full px-4 py-2.5 border border-white rounded-s-base transition
                {{ $this->viewMode === 'active'
                    ? 'bg-blue-500 text-white'
                    : 'bg-neutral-50 text-black' }}">
            Aktif
        </button>
    </li>

    <li class="w-full">
        <button wire:click="$set('viewMode', 'inactive')" type="button"
            class="w-full px-4 py-2.5 border border-white rounded-e-base transition
               {{ $this->viewMode === 'inactive'
                   ? 'bg-blue-500 text-white'
                   : 'bg-neutral-50 text-black' }}">
            Dibuang
        </button>
    </li>

</ul>
