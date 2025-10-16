<x-main-layout :title-page="__('Tambah Barang')">
    <form class="card" action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card-body">
            @include('barang.partials._form')
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-main-layout>
