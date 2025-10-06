<div class="row mb-3">
    <div class="col-md-12">
        <form action="{{ route('perbaikan.index') }}" method="GET" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <input type="text" name="search" class="form-control" placeholder="Cari nomor perbaikan atau nama barang..."
                    value="{{ request('search') }}">
            </div>
            
            <div style="width: 180px;">
                <select name="status" class="form-control">
                    <option value="">-- Semua Status --</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="width: 180px;">
                <select name="tingkat_kerusakan" class="form-control">
                    <option value="">-- Semua Tingkat --</option>
                    @foreach($tingkatOptions as $tingkat)
                        <option value="{{ $tingkat }}" {{ request('tingkat_kerusakan') == $tingkat ? 'selected' : '' }}>
                            {{ $tingkat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Cari
            </button>

            @if(request()->hasAny(['search', 'status', 'tingkat_kerusakan']))
                <a href="{{ route('perbaikan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif

            @can('manage peminjaman')
                <a href="{{ route('perbaikan.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Perbaikan
                </a>
            @endcan

            <a href="{{ route('perbaikan.laporan') }}" class="btn btn-info" target="_blank">
                <i class="fas fa-file-pdf"></i> Laporan
            </a>
        </form>
    </div>
</div>