<x-main-layout :title-page="__('Edit Peminjaman')">
    <form class="card" action="{{ route('peminjaman.update', $peminjaman->id) }}" method="POST">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Peminjaman: {{ $peminjaman->nomor_transaksi }}</h5>
                @php
                    $badgeClass = match($peminjaman->status) {
                        'Sedang Dipinjam' => 'bg-warning',
                        'Terlambat' => 'bg-danger',
                        'Sudah Dikembalikan' => 'bg-success',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $peminjaman->status }}</span>
            </div>
        </div>
        <div class="card-body">
            @method('PUT')
            @include('peminjaman.partials._form', ['update' => true])
        </div>
    </form>
</x-main-layout>