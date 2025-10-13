<x-main-layout :title-page="__('Perbaikan Barang')">
    <div class="card">
        <div class="card-body">
            @include('perbaikan.partials.toolbar')
            <x-notif-alert class="mt-4" />
            @include('perbaikan.partials.statistics')
        </div>
        @include('perbaikan.partials.list-perbaikan')
        <div class="card-body">
            {{-- Pagination dihapus karena grouping lokasi --}}
        </div>
    </div>
</x-main-layout>