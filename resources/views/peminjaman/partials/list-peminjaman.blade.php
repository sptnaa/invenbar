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
                @else
                <div>
                    <span class="badge bg-warning text-dark d-block mb-1">Sedang Dipinjam</span>
                    <span
                        class="badge bg-danger d-none badge-terlambat"
                        data-rencana="{{ $peminjaman->tanggal_kembali_rencana }}"
                        data-id="{{ $peminjaman->id }}"
                        style="cursor:pointer;">
                        Terlambat
                    </span>
                </div>
                @endif
            </td>

            <!-- Tombol Aksi -->
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
        @endforeach
    </tbody>
    @endforeach

    <!-- Modal Peringatan Keterlambatan -->
    <div class="modal fade" id="modalPeringatan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow:hidden;">

                <!-- Header -->
                <div class="modal-header text-white"
                    style="background: linear-gradient(135deg, #c0392b, #a93226); border-bottom:none;">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Peringatan Keterlambatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body text-center p-4"
                    style="background-color: #f2f2f2; color:#2b2b2b; font-family:'Poppins', sans-serif;">
                    <div class="mb-3">
                        <i class="bi bi-clock-history" style="font-size: 3rem; color:#e74c3c;"></i>
                    </div>
                    <p id="textPeringatan" style="font-size: 1.05rem; line-height:1.6; margin-bottom:10px;"></p>
                    <small style="color:#555;">
                        Mohon segera lakukan pengembalian agar sistem tetap tertib dan rapi.
                    </small>
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-center"
                    style="background-color: #f2f2f2; border-top: none;">
                    <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background-color:#c0392b; color:white; border-radius:10px; font-weight:600; padding:8px 22px; transition:all .2s;"
                        onmouseover="this.style.background='#e74c3c'" onmouseout="this.style.background='#c0392b'">
                        <i class="bi bi-check-circle me-1"></i> Mengerti
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Pengembalian -->
    <div class="modal fade" id="modalPengembalian" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formPengembalian" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Kembalikan Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kondisi_barang" class="form-label">Kondisi Barang Setelah Dipinjam</label>
                            <select name="kondisi_barang" id="kondisi_barang" class="form-control" required>
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            Pastikan kondisi barang sudah diperiksa sebelum disimpan.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Pengembalian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalEl = document.getElementById('modalPeringatan');
            const textEl = document.getElementById('textPeringatan');
            const modal = new bootstrap.Modal(modalEl);

            // pengecekan otomatis setiap detik
            setInterval(() => {
                document.querySelectorAll('.badge-terlambat').forEach(badge => {
                    const tanggalRencana = new Date(badge.dataset.rencana);
                    const now = new Date();

                    if (now > tanggalRencana) {
                        badge.classList.remove('d-none'); // badge "Terlambat"
                    }
                });
            }, 1000);

            // Saat badge "Terlambat" diklik
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('badge-terlambat')) {
                    const tanggalRencana = new Date(e.target.dataset.rencana);
                    const now = new Date();
                    const diffMs = now - tanggalRencana;

                    const totalSeconds = Math.floor(diffMs / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    // Bagian ini kamu ganti tampilannya:
                    textEl.innerHTML = `
                Barang ini sudah terlambat dikembalikan selama 
                <span style="color:#c0392b; font-weight:600;">
                    ${hours} jam ${minutes} menit ${seconds} detik
                </span>.
            `;

                    modal.show();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalPengembalian = document.getElementById('modalPengembalian');

            modalPengembalian.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');
                const form = document.getElementById('formPengembalian');
                form.action = url;
            });
        });
    </script>

</x-table-list>