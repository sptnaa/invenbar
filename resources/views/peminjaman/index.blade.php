<x-main-layout :title-page="__('Peminjaman Barang')">
    <div class="card">
        <div class="card-body">
            <!-- Toolbar -->
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

            <x-notif-alert class="mb-4" />

            <!-- Statistik Cards -->
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
        </div>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-right">
                    <tr>
                        <th width="4%">#</th>
                        <th width="12%">No. Transaksi</th>
                        <th width="18%">Peminjam</th>
                        <th width="20%">Barang</th>
                        <th width="8%">Jumlah</th>
                        <th width="10%">Tgl. Pinjam</th>
                        <th width="10%">Tgl. Kembali</th>
                        <th width="8%">Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $index => $peminjaman)
                    <tr>
                        <td class="text-center align-middle">{{ $peminjamans->firstItem() + $index }}</td>
                        <td class="align-middle">
                            <strong class="text-primary">{{ $peminjaman->nomor_transaksi }}</strong>
                        </td>
                        <td class="align-middle">
                            <div>
                                <strong class="text-dark">{{ $peminjaman->nama_peminjam }}</strong>
                                @if($peminjaman->email_peminjam)
                                <br><small class="text-muted"><i class="fas fa-envelope me-1"></i>{{ $peminjaman->email_peminjam }}</small>
                                @endif
                                @if($peminjaman->telepon_peminjam)
                                <br><small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $peminjaman->telepon_peminjam }}</small>
                                @endif
                            </div>
                        </td>
                        <td class="align-middle">
                            <div>
                                <strong class="text-dark">{{ $peminjaman->barang->nama_barang }}</strong>
                                <br><small class="text-primary">{{ $peminjaman->barang->kode_barang }}</small>
                                <br><small class="badge bg-light text-dark">{{ $peminjaman->barang->kategori->nama_kategori }}</small>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge bg-info text-dark">
                                {{ $peminjaman->jumlah_pinjam }} {{ $peminjaman->barang->satuan }}
                            </span>
                        </td>
                        <td class="text-center align-middle">
                            <span class="text-dark fw-bold">{{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</span>
                        </td>
                        <td class="text-center align-middle">
                            <div>
                                <span class="text-dark fw-bold">{{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}</span>
                                @if($peminjaman->tanggal_kembali_aktual)
                                <br><small class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>{{ $peminjaman->tanggal_kembali_aktual->format('d/m/Y') }}
                                </small>
                                @endif
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <div>
                                @php
                                $badgeClass = match($peminjaman->status) {
                                'Sedang Dipinjam' => 'bg-warning text-dark',
                                'Terlambat' => 'bg-danger text-white',
                                'Sudah Dikembalikan' => 'bg-success text-white',
                                default => 'bg-secondary text-white'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-3 py-2">
                                    @if($peminjaman->status === 'Sedang Dipinjam')
                                    <i class="fas fa-clock me-1"></i>
                                    @elseif($peminjaman->status === 'Terlambat')
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    @elseif($peminjaman->status === 'Sudah Dikembalikan')
                                    <i class="fas fa-check-circle me-1"></i>
                                    @endif
                                    {{ $peminjaman->status }}
                                </span>
                                @if($peminjaman->terlambat && $peminjaman->status !== 'Sudah Dikembalikan')
                                <br><small class="text-danger fw-bold mt-1 d-block">
                                    <i class="fas fa-calendar-times me-1"></i>{{ $peminjaman->hari_terlambat }} hari terlambat
                                </small>
                                @endif
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <div class="d-flex justify-content-center gap-2">
                                @can('view peminjaman')
                                <x-tombol-aksi :href="route('peminjaman.show', $peminjaman->id)" type="show" />
                                @endcan

                                @if($peminjaman->status !== 'Sudah Dikembalikan')
                                @can('manage peminjaman')
                                <x-tombol-aksi :href="route('peminjaman.edit', $peminjaman->id)" type="edit" />
                                @endcan
                                @endif

                                @can('delete peminjaman')
                                <x-tombol-aksi :href="route('peminjaman.destroy', $peminjaman->id)" type="delete" />
                                @endcan

                                @if($peminjaman->status !== 'Sudah Dikembalikan')
                                @can('manage peminjaman')
                                <form action="{{ route('peminjaman.pengembalian', $peminjaman->id) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-tombol-aksi type="return" />
                                </form>
                                @endcan
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada data peminjaman</h5>
                                <p class="text-muted">Data peminjaman akan muncul di sini setelah ditambahkan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $peminjamans->links() }}
        </div>
    </div>
</x-main-layout>