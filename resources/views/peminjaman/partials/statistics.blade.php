@php
    $totalData = $peminjamans->total();
    $sedangDipinjamCount = $peminjamans->where('status', 'Sedang Dipinjam')->count();
    $terlambatCount = $peminjamans->where('status', 'Terlambat')->count();
    $sudahDikembalikanCount = $peminjamans->where('status', 'Sudah Dikembalikan')->count();
@endphp

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Peminjaman</h5>
                <h3>{{ $totalData }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h5>Sedang Dipinjam</h5>
                <h3>{{ $sedangDipinjamCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h5>Terlambat</h5>
                <h3>{{ $terlambatCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Sudah Dikembalikan</h5>
                <h3>{{ $sudahDikembalikanCount }}</h3>
            </div>
        </div>
    </div>
</div>