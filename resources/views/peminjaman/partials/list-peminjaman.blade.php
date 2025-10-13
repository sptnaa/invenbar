<x-table-list>
    <x-slot name="header">
        <tr>
            <th width="4%">#</th>
            <th width="12%">No. Peminjaman</th>
            <th width="18%">Peminjam<br><small class="text-muted">Email & Telepon</small></th>
            <th width="20%">Barang<br><small class="text-muted">Kode & Kategori</small></th>
            <th width="8%">Jumlah</th>
            <th width="10%">Tgl. Pinjam</th>
            <th width="10%">Tgl. Kembali</th>
            <th width="8%">Status</th>
            <th width="10%">Aksi</th>
        </tr>
    </x-slot>

    @php
        // Kelompokkan peminjaman berdasarkan nama lokasi barang
        $grouped = $peminjamans->groupBy(fn($item) => $item->barang->lokasi->nama_lokasi ?? 'Tidak Ada Lokasi');
    @endphp

    @foreach ($grouped as $lokasi => $items)
        <tr class="table-light">
            <td colspan="9">
                <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="collapse"
                    data-bs-target="#lokasi{{ Str::slug($lokasi) }}">
                    <i class="fas fa-map-marker-alt"></i> {{ strtoupper($lokasi) }}
                </button>
            </td>
        </tr>

        <tbody id="lokasi{{ Str::slug($lokasi) }}" class="collapse show">
            @foreach ($items as $index => $peminjaman)
                <tr>
                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                    <td class="align-middle">
                        <strong class="text-primary">{{ $peminjaman->nomor_transaksi }}</strong>
                    </td>

                    <!-- Kolom Peminjam -->
                    <td class="align-middle">
                        <strong>{{ $peminjaman->nama_peminjam }}</strong><br>
                        <small class="text-muted">{{ $peminjaman->email_peminjam }}</small><br>
                        <small class="text-muted">{{ $peminjaman->telepon_peminjam ?? '-' }}</small>
                    </td>

                    <!-- Kolom Barang -->
                    <td class="align-middle">
                        @if ($peminjaman->barang)
                            <strong>{{ $peminjaman->barang->nama_barang }}</strong><br>
                            <small>{{ $peminjaman->barang->kode_barang }}</small><br>
                            <small class="text-muted">
                                {{ $peminjaman->barang->kategori->nama_kategori ?? '-' }}
                            </small>
                        @else
                            <em class="text-muted">Barang tidak ditemukan</em>
                        @endif
                    </td>

                    <td class="text-center align-middle">{{ $peminjaman->jumlah_pinjam }}</td>
                    <td class="text-center align-middle">{{ $peminjaman->tanggal_pinjam_formatted }}</td>
                    <td class="text-center align-middle">{{ $peminjaman->tanggal_kembali_rencana_formatted }}</td>

                    <!-- Status -->
                    <td class="text-center align-middle">
                        @if ($peminjaman->status === 'Sudah Dikembalikan')
                            <span class="badge bg-success">Dikembalikan</span>
                        @elseif ($peminjaman->status === 'Terlambat')
                            <span class="badge bg-danger">Terlambat</span>
                        @else
                            <span class="badge bg-warning text-dark">Sedang Dipinjam</span>
                        @endif
                    </td>

                    <!-- Tombol Aksi -->
                    <td class="text-center align-middle">
                        <x-tombol-aksi :href="route('peminjaman.show', $peminjaman->id)" type="show" />

                        @if ($peminjaman->status !== 'Sudah Dikembalikan')
                            <x-tombol-aksi :href="route('peminjaman.edit', $peminjaman->id)" type="edit" />
                        @endif

                        <x-tombol-aksi :href="route('peminjaman.destroy', $peminjaman->id)" type="delete" />
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endforeach

    <!-- Modal Pengembalian -->
    <div class="modal fade" id="modalPengembalian" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formPengembalian" method="POST">
                    @csrf
                    @method('PATCH') {{-- route pakai PATCH --}}

                    <div class="modal-header">
                        <h5 class="modal-title">Kembalikan Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kondisi_barang" class="form-label">Kondisi Setelah Dipinjam</label>
                            <select name="kondisi_barang" id="kondisi_barang" class="form-select" required>
                                <option value="" disabled selected>Pilih Kondisi</option>
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
