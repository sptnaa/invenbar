<div class="d-flex justify-content-between align-items-center mb-3">
    {{-- Tombol di sebelah kiri --}}
    <div>
        @can('manage peminjaman')
            <a href="{{ route('perbaikan.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-square"></i> Tambah Perbaikan
            </a>
        @endcan

        <a href="{{ route('perbaikan.laporan') }}" class="btn btn-success" target="_blank">
            <i class="bi bi-printer"></i> Cetak Laporan Perbaikan
        </a>
    </div>

    {{-- Form pencarian di sebelah kanan --}}
    <form action="{{ route('perbaikan.index') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control"
            placeholder="Cari nomor perbaikan atau nama barang..."
            value="{{ request('search') }}" style="width: 250px;">

        <select name="status" class="form-select" style="width: 160px;">
            <option value="">-- Semua Status --</option>
            @foreach($statusOptions as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                    {{ $status }}
                </option>
            @endforeach
        </select>

        <select name="tingkat_kerusakan" class="form-select" style="width: 160px;">
            <option value="">-- Semua Tingkat --</option>
            @foreach($tingkatOptions as $tingkat)
                <option value="{{ $tingkat }}" {{ request('tingkat_kerusakan') == $tingkat ? 'selected' : '' }}>
                    {{ $tingkat }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Cari
        </button>

        @if(request()->hasAny(['search', 'status', 'tingkat_kerusakan']))
            <a href="{{ route('perbaikan.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reset
            </a>
        @endif
    </form>
</div>
