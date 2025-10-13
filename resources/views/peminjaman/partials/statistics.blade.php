@php
    $totalData = $peminjamans->total();

    $sedangDipinjamCount = $peminjamans->filter(function ($item) {
        return in_array($item->status, ['Sedang Dipinjam', 'Terlambat']);
    })->count();

    $terlambatCount = $peminjamans->filter(function ($item) {
        return $item->status === 'Terlambat';
    })->count();

    $sudahDikembalikanCount = $peminjamans->filter(function ($item) {
        return $item->status === 'Sudah Dikembalikan';
    })->count();
@endphp

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white text-center">
            <div class="card-body">
                <h5>Total Peminjaman</h5>
                <h3>{{ $total }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-white text-center">
            <div class="card-body">
                <h5>Sedang Dipinjam</h5>
                <h3>{{ $sedangDipinjam }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-danger text-white text-center">
            <div class="card-body">
                <h5>Terlambat</h5>
                <h3>{{ $terlambat }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white text-center">
            <div class="card-body">
                <h5>Sudah Dikembalikan</h5>
                <h3>{{ $sudahDikembalikan }}</h3>
            </div>
        </div>
    </div>
</div>
