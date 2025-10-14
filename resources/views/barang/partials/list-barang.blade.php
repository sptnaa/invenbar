<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="4%">#</th>
                    <th width="12%">Kode Barang</th>
                    <th width="20%">Nama Barang</th>
                    <th width="15%">Kategori</th>
                    <th width="10%">Jumlah</th>
                    <th width="10%">Kondisi</th>
                    <th width="10%">Status</th>
                    <th width="10%">Sumber</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($grouped as $lokasiNama => $barangGroup)
                {{-- Tombol Lokasi --}}
                <tr class="bg-light">
                    <td colspan="9">
                        <button class="btn btn-sm btn-outline-primary fw-normal lokasi-toggle"
                            data-lokasi="{{ Str::slug($lokasiNama) }}">
                            {{ strtoupper($lokasiNama) }}
                        </button>
                    </td>
                </tr>

                {{-- Daftar Barang per Lokasi --}}
            <tbody id="lokasi-{{ Str::slug($lokasiNama) }}" class="lokasi-row">
                @foreach ($barangGroup as $barang)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $barang->kode_barang }}</strong></td>
                    <td>{{ $barang->nama_barang }}</td>
                    <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                    <td>{{ $barang->stok_tersedia }} {{ $barang->satuan }}</td>
                    <td>
                        @foreach($barang->kondisi_array as $kondisi)
                        <span class="badge 
                                            @if($kondisi['class'] == 'condition-baik') bg-success 
                                            @elseif($kondisi['class'] == 'condition-rusak-ringan') bg-warning text-dark
                                            @elseif($kondisi['class'] == 'condition-rusak-berat') bg-danger
                                            @endif">
                            {{ $kondisi['label'] }}
                        </span>
                        @endforeach
                    </td>
                    <td>
                        @if ($barang->sedang_dipinjam)
                        <span class="badge bg-warning text-dark">Dipinjam</span>
                        @elseif ($barang->sedang_diperbaiki)
                        <span class="badge bg-danger">Diperbaiki</span>
                        @else
                        <span class="badge bg-success">Tersedia</span>
                        @endif
                    </td>
                    <td>{{ $barang->sumber ?? '-' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            @can('manage barang')
                            <x-tombol-aksi href="{{ route('barang.show', $barang->id) }}" type="show" />
                            <x-tombol-aksi href="{{ route('barang.edit', $barang->id) }}" type="edit" />
                            @endcan
                            @can('delete barang')
                            <x-tombol-aksi href="{{ route('barang.destroy', $barang->id) }}" type="delete" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    Belum ada data barang. Silakan tambahkan barang baru.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Script untuk toggle lokasi --}}
<script>
    document.querySelectorAll('.lokasi-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const lokasiId = button.dataset.lokasi;
            const target = document.getElementById('lokasi-' + lokasiId);
            target.classList.toggle('d-none');
        });
    });

    // Default: semua lokasi disembunyikan
    document.querySelectorAll('.lokasi-row').forEach(row => row.classList.add('d-none'));
</script>

{{-- Tambahkan style agar tabel tidak ada garis --}}
<style>
    .table,
    .table th,
    .table td {
        border: none !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f9fafb;
    }
</style>