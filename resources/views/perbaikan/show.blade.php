<x-main-layout :title-page="__('Detail Data Perbaikan')">
    <div class="card">
        <div class="card-body">
            {{-- Panggil partial untuk menampilkan informasi --}}
            @include('perbaikan.partials.info_data_perbaikan', ['perbaikan' => $perbaikan])

            <div class="mt-4">
                <x-tombol-kembali :href="route('perbaikan.index')" />
                <a href="{{ route('perbaikan.edit', $perbaikan->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
    </div>
</x-main-layout>
