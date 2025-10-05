<x-main-layout :title-page="__('Peminjaman Barang')">
    <div class="card">
        <div class="card-body">
            @include('peminjaman.partials.toolbar')
            <x-notif-alert class="mt-4" />
            @include('peminjaman.partials.statistics')
        </div>
        @include('peminjaman.partials.list-peminjaman')
        <div class="card-body">
            {{ $peminjamans->links() }}
        </div>
    </div>
</x-main-layout>