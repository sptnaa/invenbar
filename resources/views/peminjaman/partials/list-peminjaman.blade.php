<x-table-list>
    <x-slot name="header">
        <tr>
            <th width="4%">#</th>
            <th width="12%">No. Peminjaman</th>
            <th width="18%">Peminjam</th>
            <th width="20%">Barang</th>
            <th width="8%">Jumlah</th>
            <th width="10%">Tgl. Pinjam</th>
            <th width="10%">Tgl. Kembali</th>
            <th width="8%">Status</th>
            <th width="10%">Aksi</th>
        </tr>
    </x-slot>

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
            <div>
                <span class="text-dark fw-bold">{{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</span>
                <br>
                <small class="text-muted">{{ $peminjaman->tanggal_pinjam->format('H:i') }}</small>
            </div>
        </td>
        <td class="text-center align-middle">
            <div>
                <span class="text-dark fw-bold">{{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}</span>
                <br>
                <small class="text-muted">{{ $peminjaman->tanggal_kembali_rencana->format('H:i') }}</small>

                @if($peminjaman->tanggal_kembali_aktual)
                <br><small class="badge bg-success">
                    <i class="fas fa-check me-1"></i>{{ $peminjaman->tanggal_kembali_aktual->format('d/m/Y H:i') }}
                </small>
                @endif
            </div>
        </td>

        <td class="text-center align-middle">
            @php
            $status = $peminjaman->status;
            $badgeClass = match ($status) {
            'Sedang Dipinjam' => 'bg-warning text-dark',
            'Sudah Dikembalikan' => 'bg-success text-white',
            'Terlambat' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
            };
            @endphp

            <span class="badge rounded-pill {{ $badgeClass }}">
                {{ $status }}
            </span>

            @if($status === 'Terlambat' && $peminjaman->hari_terlambat > 0)
            <br>
            <small class="text-danger fw-bold">
                <i class="fas fa-clock me-1"></i>{{ $peminjaman->hari_terlambat }} hari
            </small>
            @endif
        </td>
        <td class="align-middle text-center">
            <div class="d-flex justify-content-center gap-1 mb-1">
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
            </div>

            @if($peminjaman->status !== 'Sudah Dikembalikan')
            @can('manage peminjaman')
            <form action="{{ route('peminjaman.pengembalian', $peminjaman->id) }}"
                method="POST" class="d-inline w-100">
                @csrf
                @method('PATCH')
                <button type="button"
                    class="btn btn-success btn-sm w-100"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPengembalian"
                    data-url="{{ route('peminjaman.pengembalian', $peminjaman->id) }}">
                    <i class="fas fa-undo"></i> Kembalikan
                </button>

            </form>
            @endcan
            @endif
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

    <!-- Modal Pengembalian -->
    <div class="modal fade" id="modalPengembalian" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formPengembalian" method="POST">
                    @csrf
                    @method('PATCH') {{-- karena route kamu pakai PATCH --}}

                    <div class="modal-header">
                        <h5 class="modal-title">Kembalikan Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kondisi_barang" class="form-label">Kondisi Setelah Dipinjam</label>
                            <select name="kondisi_barang" id="kondisi_barang" class="form-control" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalPengembalian = document.getElementById('modalPengembalian');
            modalPengembalian.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');
                const form = document.getElementById('formPengembalian');
                form.setAttribute('action', url);
            });
        });
    </script>

</x-table-list>