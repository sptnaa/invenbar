<x-main-layout :title-page="__('Tambah Peminjaman')">
    @if ($errors->any())
        <div class="alert alert-danger mx-3 mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form class="card" action="{{ route('peminjaman.store') }}" method="POST">
        <div class="card-header">
            <h5 class="mb-0">Form Peminjaman Barang</h5>
        </div>
        <div class="card-body">
            @include('peminjaman.partials._form')
        </div>
    </form>
</x-main-layout>