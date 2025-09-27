<x-main-layout :title-page="__('Tambah Peminjaman')">
    <form class="card" action="{{ route('peminjaman.store') }}" method="POST">
        <div class="card-header">
            <h5 class="mb-0">Form Peminjaman Barang</h5>
        </div>
        <div class="card-body">
            @include('peminjaman.partials._form')
        </div>
    </form>
</x-main-layout>