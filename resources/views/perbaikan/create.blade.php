<x-main-layout :title-page="__('Tambah Data Perbaikan')">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('perbaikan.store') }}" method="POST">
                @include('perbaikan.partials._form')
            </form>
        </div>
    </div>
</x-main-layout>