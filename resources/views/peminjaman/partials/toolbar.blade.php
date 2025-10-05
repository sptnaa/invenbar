<div class="row mb-4">
    <div class="col">
        @can('manage peminjaman')
            <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Peminjaman
            </a>
        @endcan
        
        <a href="{{ route('peminjaman.laporan') }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print"></i> Cetak Laporan
        </a>
    </div>

    <div class="col-auto">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" 
                   placeholder="Cari peminjam/nomor transaksi..." 
                   value="{{ request('search') }}" style="min-width: 250px;">
            
            <select name="status" class="form-select" style="min-width: 150px;">
                <option value="">Semua Status</option>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Cari
            </button>
            
            @if(request('search') || request('status'))
                <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>