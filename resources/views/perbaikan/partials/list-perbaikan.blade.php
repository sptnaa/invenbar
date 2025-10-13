<x-table-list>
    <x-slot name="header">
        <tr>
            <th width="4%">#</th>
            <th width="12%">No. Perbaikan</th>
            <th width="18%">Barang</th>
            <th width="8%">Jumlah</th>
            <th width="12%">Tingkat Kerusakan</th>
            <th width="10%">Tgl. Masuk</th>
            <th width="10%">Durasi</th>
            <th width="10%">Status</th>
            <th width="10%">Biaya</th>
            <th width="12%">Aksi</th>
        </tr>
    </x-slot>

    @foreach ($groupedPerbaikans as $lokasi => $perbaikans)
    {{-- HEADER GROUPING --}}
    <tr class="table-secondary">
        <td colspan="10" class="fw-bold">
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#lokasi-{{ Str::slug($lokasi) }}">
                {{ strtoupper($lokasi) }}
            </button>
        </td>
    </tr>

    {{-- ISI DATA PERBAIKAN --}}
    <tbody id="lokasi-{{ Str::slug($lokasi) }}" class="collapse show">
        @forelse ($perbaikans as $index => $perbaikan)
            <tr>
                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                <td class="align-middle">
                    <strong class="text-primary">{{ $perbaikan->nomor_perbaikan }}</strong>
                    @if($perbaikan->peminjaman)
                        <br><small class="text-muted">Dari: {{ $perbaikan->peminjaman->nomor_transaksi }}</small>
                    @endif
                </td>
                <td class="align-middle">
                    <strong>{{ $perbaikan->barang->nama_barang }}</strong>
                    <br><small class="text-primary">{{ $perbaikan->barang->kode_barang }}</small>
                    <br><small class="badge bg-light text-dark">{{ $perbaikan->barang->kategori->nama_kategori }}</small>
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-info text-dark">
                        {{ $perbaikan->jumlah_rusak }} {{ $perbaikan->barang->satuan }}
                    </span>
                </td>
                <td class="text-center align-middle">
                    <span class="badge {{ $perbaikan->kerusakan_badge_class }}">
                        {{ $perbaikan->tingkat_kerusakan }}
                    </span>
                </td>
                <td class="text-center align-middle">
                    <span class="text-dark fw-bold">{{ $perbaikan->tanggal_masuk->format('d/m/Y') }}</span>
                    @if($perbaikan->tanggal_selesai)
                        <br><small class="badge bg-success">
                            <i class="fas fa-check me-1"></i>{{ $perbaikan->tanggal_selesai->format('d/m/Y') }}
                        </small>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <small class="text-muted">{{ $perbaikan->durasi_perbaikan }}</small>
                </td>
                <td class="text-center align-middle">
                    <span class="badge rounded-pill {{ $perbaikan->status_badge_class }}">
                        {{ $perbaikan->status }}
                    </span>
                </td>
                <td class="text-end align-middle">
                    @if($perbaikan->biaya_perbaikan)
                        <strong>Rp {{ number_format($perbaikan->biaya_perbaikan, 0, ',', '.') }}</strong>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center gap-1">
                        @can('view peminjaman')
                            <x-tombol-aksi :href="route('perbaikan.show', $perbaikan->id)" type="show" />
                        @endcan

                        @if($perbaikan->status !== 'Selesai')
                            @can('manage peminjaman')
                                <x-tombol-aksi :href="route('perbaikan.edit', $perbaikan->id)" type="edit" />
                            @endcan
                        @endif

                        @can('delete peminjaman')
                            <x-tombol-aksi :href="route('perbaikan.destroy', $perbaikan->id)" type="delete" />
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-3 text-muted">Tidak ada data perbaikan di lokasi ini</td>
            </tr>
        @endforelse
    </tbody>
@endforeach


    <!-- Modal Proses Perbaikan -->
    <div class="modal fade" id="modalProsesPerbaikan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formProsesPerbaikan" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <h5 class="modal-title">Proses Perbaikan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong id="detailNomor"></strong><br>
                            <span id="detailBarang"></span>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status Perbaikan <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Dalam Perbaikan">Dalam Perbaikan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_perbaikan" class="form-label">Catatan Perbaikan</label>
                            <textarea name="catatan_perbaikan" id="catatan_perbaikan" class="form-control" rows="3" 
                                placeholder="Masukkan catatan perbaikan..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="biaya_perbaikan" class="form-label">Biaya Perbaikan (Rp)</label>
                            <input type="number" name="biaya_perbaikan" id="biaya_perbaikan" class="form-control" 
                                min="0" step="1000" placeholder="0">
                        </div>

                        <div class="alert alert-warning" id="alertSelesai" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Jika status diubah menjadi "Selesai", maka:
                            <ul class="mb-0 mt-2">
                                <li>Barang akan dipindahkan ke stok kondisi "Baik"</li>
                                <li>Stok rusak akan berkurang otomatis</li>
                            </ul>
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
            const modal = document.getElementById('modalProsesPerbaikan');
            const statusSelect = document.getElementById('status');
            const alertSelesai = document.getElementById('alertSelesai');

            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nomor = button.getAttribute('data-nomor');
                const barang = button.getAttribute('data-barang');
                const currentStatus = button.getAttribute('data-status');

                document.getElementById('detailNomor').textContent = nomor;
                document.getElementById('detailBarang').textContent = barang;

                const form = document.getElementById('formProsesPerbaikan');
                form.setAttribute('action', `/perbaikan/${id}`);

                // Reset form
                document.getElementById('catatan_perbaikan').value = '';
                document.getElementById('biaya_perbaikan').value = '';
                
                // Set default status
                if (currentStatus === 'Menunggu') {
                    statusSelect.value = 'Dalam Perbaikan';
                }
            });

            // Show alert when status is "Selesai"
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Selesai') {
                    alertSelesai.style.display = 'block';
                } else {
                    alertSelesai.style.display = 'none';
                }
            });
        });
    </script>

</x-table-list>