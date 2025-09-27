<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th width="5%">#</th>
                <th width="12%">No. Transaksi</th>
                <th width="15%">Peminjam</th>
                <th width="20%">Barang</th>
                <th width="8%">Jumlah</th>
                <th width="10%">Tgl. Pinjam</th>
                <th width="10%">Tgl. Kembali</th>
                <th width="10%">Status</th>
                <th width="10%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjamans as $index => $peminjaman)
                <tr class="{{ $peminjaman->status === 'Terlambat' ? 'table-danger' : '' }}">
                    <td>{{ $peminjamans->firstItem() + $index }}</td>
                    <td>
                        <strong>{{ $peminjaman->nomor_transaksi }}</strong>
                    </td>
                    <td>
                        <strong>{{ $peminjaman->nama_peminjam }}</strong>
                        @if($peminjaman->email_peminjam)
                            <br><small class="text-muted">{{ $peminjaman->email_peminjam }}</small>
                        @endif
                        @if($peminjaman->telepon_peminjam)
                            <br><small class="text-muted">{{ $peminjaman->telepon_peminjam }}</small>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $peminjaman->barang->nama_barang }}</strong>
                        <br><small class="text-muted">{{ $peminjaman->barang->kode_barang }}</small>
                        <br><small class="text-muted">{{ $peminjaman->barang->kategori->nama_kategori }}</small>
                    </td>
                    <td class="text-center">
                        {{ $peminjaman->jumlah_pinjam }} {{ $peminjaman->barang->satuan }}
                    </td>
                    <td class="text-center">{{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</td>
                    <td class="text-center">
                        {{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}
                        @if($peminjaman->tanggal_kembali_aktual)
                            <br><small class="text-success">
                                Actual: {{ $peminjaman->tanggal_kembali_aktual->format('d/m/Y') }}
                            </small>
                        @endif
                        @if($peminjaman->terlambat && $peminjaman->status !== 'Sudah Dikembalikan')
                            <br><small class="text-danger">{{ $peminjaman->hari_terlambat }} hari terlambat</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $badgeClass = match($peminjaman->status) {
                                'Sedang Dipinjam' => 'bg-warning text-dark',
                                'Terlambat' => 'bg-danger',
                                'Sudah Dikembalikan' => 'bg-success',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $peminjaman->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            @can('view peminjaman')
                                <a href="{{ route('peminjaman.show', $peminjaman->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endcan
                            
                            @if($peminjaman->status !== 'Sudah Dikembalikan')
                                @can('manage peminjaman')
                                    <a href="{{ route('peminjaman.edit', $peminjaman->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                            @endif

                            @can('delete peminjaman')
                                <form action="{{ route('peminjaman.destroy', $peminjaman->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Yakin hapus data peminjaman ini?')"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                        
                        @if($peminjaman->status !== 'Sudah Dikembalikan')
                            @can('manage peminjaman')
                                <form action="{{ route('peminjaman.pengembalian', $peminjaman->id) }}" 
                                      method="POST" class="d-inline mt-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm w-100" 
                                            onclick="return confirm('Konfirmasi pengembalian barang?')"
                                            title="Kembalikan Barang">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Belum ada data peminjaman.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>