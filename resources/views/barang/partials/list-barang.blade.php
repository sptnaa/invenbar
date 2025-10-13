<style>
    .condition-badges {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .condition-badge {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 600;
        text-align: center;
        min-width: 50px;
        backdrop-filter: blur(10px);
    }

    .condition-baik {
        color: #28a745;
        border-color: #28a745;
    }

    .condition-rusak-ringan {
        color: #ffc107;
        border-color: #ffc107;
    }

    .condition-rusak-berat {
        color: #dc3545;
        border-color: #dc3545;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="card-body p-0">
    <div class="row p-4">
        @forelse ($barangs as $barang)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 position-relative card-hover">
                <div class="condition-badges">
                    @foreach($barang->kondisi_array as $kondisi)
                    <div class="condition-badge {{ $kondisi['class'] }}">
                        {{ $kondisi['label'] }}
                    </div>
                    @endforeach
                </div>
                @if ($barang->sedang_dipinjam)
                <div class="condition-badge bg-warning text-dark position-absolute bottom-0 start-0 m-2 px-2 py-1 rounded">
                    Barang sedang dipinjam
                </div>
                @endif

                @if ($barang->sedang_diperbaiki)
                <div class="condition-badge bg-danger text-white position-absolute bottom-0 start-0 m-2 px-2 py-1 rounded">
                    Barang dalam perbaikan
                </div>
                @endif

                <div class="card-header bg-light">
                    <span class="badge bg-primary mb-2">{{ $barang->kode_barang }}</span>
                    <h5 class="card-title mb-0">{{ $barang->nama_barang }}</h5>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">KATEGORI</small>
                        <strong>{{ $barang->kategori->nama_kategori }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">LOKASI</small>
                        <strong>{{ $barang->lokasi->nama_lokasi }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">TOTAL</small>
                        <strong>{{ $barang->stok_tersedia }} {{ $barang->satuan }}</strong>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-end gap-1">
                    @can('manage barang')
                    <x-tombol-aksi href="{{ route('barang.show', $barang->id) }}" type="show" />
                    <x-tombol-aksi href="{{ route('barang.edit', $barang->id) }}" type="edit" />
                    @endcan

                    @can('delete barang')
                    <x-tombol-aksi href="{{ route('barang.destroy', $barang->id) }}" type="delete" />
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h5>📦 Data barang belum tersedia</h5>
                <p class="mb-0">Silakan tambahkan barang baru untuk memulai.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>