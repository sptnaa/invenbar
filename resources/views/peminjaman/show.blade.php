<x-main-layout :title-page="__('Detail Data Peminjaman')">
    <div class="card">
        <div class="card-body">
            {{-- Panggil partial untuk menampilkan informasi detail peminjaman --}}
            @include('peminjaman.partials.info-data-peminjaman', ['peminjaman' => $peminjaman])

            <div class="mt-4 d-flex gap-2">
                <x-tombol-kembali :href="route('peminjaman.index')" />

                @if($peminjaman->status !== 'Sudah Dikembalikan')
                    @can('manage peminjaman')
                        <a href="{{ route('peminjaman.edit', $peminjaman->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('peminjaman.pengembalian', $peminjaman->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Konfirmasi pengembalian barang?')">
                                <i class="fas fa-undo me-1"></i> Kembalikan Barang
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</x-main-layout>
