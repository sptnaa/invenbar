<x-main-layout :title-page="__('Edit Barang')">
    <form class="card" action="{{ route('barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('barang.partials._form', ['update' => true])
        </div>
    </form>
</x-main-layout>
