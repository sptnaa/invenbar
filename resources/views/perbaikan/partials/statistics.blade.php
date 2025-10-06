@php
    $totalPerbaikan = App\Models\Perbaikan::count();
    $menunggu = App\Models\Perbaikan::where('status', 'Menunggu')->count();
    $dalamPerbaikan = App\Models\Perbaikan::where('status', 'Dalam Perbaikan')->count();
    $selesai = App\Models\Perbaikan::where('status', 'Selesai')->count();
    $totalBiaya = App\Models\Perbaikan::where('status', 'Selesai')->sum('biaya_perbaikan');
@endphp

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-tools fa-3x text-primary mb-2"></i>
                <h3 class="mb-0">{{ $totalPerbaikan }}</h3>
                <p class="text-muted mb-0">Total Perbaikan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                <h3 class="mb-0">{{ $menunggu }}</h3>
                <p class="text-muted mb-0">Menunggu</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-wrench fa-3x text-info mb-2"></i>
                <h3 class="mb-0">{{ $dalamPerbaikan }}</h3>
                <p class="text-muted mb-0">Dalam Perbaikan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <h3 class="mb-0">{{ $selesai }}</h3>
                <p class="text-muted mb-0">Selesai</p>
                <small class="text-muted">Biaya: Rp {{ number_format($totalBiaya, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>
</div>