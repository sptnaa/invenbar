<div x-data="{ openDropdowns: {} }">
    <x-table-list>
        <x-slot name="header">
            <tr>
                <th>#</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Sumber</th>
                <th class="text-center">Aksi</th>
            </tr>
        </x-slot>

        @if(isset($barangs) && count($barangs) > 0)
            @foreach ($barangs as $index => $barang)
                @php
                    $hasChildUnits = $barang->mode_input === 'unit' && $barang->childUnits && $barang->childUnits->count() > 0;
                    $totalUnits = $hasChildUnits ? ($barang->childUnits->count() + 1) : 1;
                @endphp
                
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- Kolom Kode Barang + Tombol Dropdown --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <strong>{{ $barang->kode_barang }}</strong>

                            {{-- Tombol dropdown hanya muncul jika mode unit dan ada child --}}
                            @if($hasChildUnits)
                                <button
                                    @click="openDropdowns['{{ $barang->id }}'] = !openDropdowns['{{ $barang->id }}']"
                                    class="btn btn-sm btn-outline-primary py-0 px-2"
                                    style="min-width: 28px;"
                                    type="button"
                                >
                                    <span x-text="openDropdowns['{{ $barang->id }}'] ? '▲' : '▼'">▼</span>
                                </button>

                                <span class="badge bg-info text-white">
                                    {{ $totalUnits }} Unit
                                </span>
                            @endif

                            {{-- Badge untuk mode unit --}}
                            @if($barang->mode_input === 'unit')
                                <span class="badge bg-primary" style="font-size: 9px;">Per Unit</span>
                            @endif
                        </div>
                    </td>

                    <td>{{ $barang->nama_barang }}</td>
                    <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                    <td>
                        @if($barang->mode_input === 'unit')
                            {{ $totalUnits }} {{ $barang->satuan }}
                        @else
                            {{ $barang->stok_tersedia }} {{ $barang->satuan }}
                        @endif
                    </td>

                    {{-- Kondisi (gabungan parent + child) --}}
                    <td>
                        @php
                            $kondisiSummary = $barang->kondisi_summary ?? [];
                        @endphp

                        @foreach($kondisiSummary as $label => $jumlah)
                            <span class="badge 
                                @if($label == 'Baik') bg-success
                                @elseif($label == 'Rusak Ringan') bg-warning text-dark
                                @else bg-danger
                                @endif">
                                {{ $label }} ({{ $jumlah }})
                            </span>
                        @endforeach
                    </td>

                    {{-- Status --}}
                    <td>
                        @if ($barang->is_pinjaman)
                            @if ($barang->sedang_dipinjam)
                                <span class="badge bg-warning text-dark">Dipinjam</span>
                            @elseif ($barang->sedang_diperbaiki)
                                <span class="badge bg-danger">Diperbaiki</span>
                            @else
                                <span class="badge bg-success">Bisa Dipinjamkan</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Tidak Dipinjamkan</span>
                        @endif
                    </td>

                    <td>{{ $barang->sumber ?? '-' }}</td>

                    {{-- Aksi --}}
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            <x-tombol-aksi href="{{ route('barang.show', $barang->id) }}" type="show" />
                            <x-tombol-aksi href="{{ route('barang.edit', $barang->id) }}" type="edit" />
                            <x-tombol-aksi href="{{ route('barang.destroy', $barang->id) }}" type="delete" />
                        </div>
                    </td>
                </tr>

                {{-- Row Anak (Child Units) - Hanya untuk mode unit --}}
                @if($hasChildUnits)
                    <tr x-show="openDropdowns['{{ $barang->id }}']" 
                        x-transition
                        style="display: none;">
                        <td colspan="9" class="bg-light p-0">
                            <div class="p-3">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-box-seam"></i> Detail {{ $totalUnits - 1 }} Unit Barang
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 20%;">Kode Unit</th>
                                                <th style="width: 15%;">Kondisi</th>
                                                <th style="width: 15%;">Status</th>
                                                <th style="width: 15%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Child units --}}
                                            @foreach($barang->childUnits as $child)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><code>{{ $child->kode_barang }}</code></td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($child->kondisi_dominan == 'Baik') bg-success
                                                            @elseif($child->kondisi_dominan == 'Rusak Ringan') bg-warning text-dark
                                                            @else bg-danger
                                                            @endif">
                                                            {{ $child->kondisi_dominan }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($child->sedang_dipinjam)
                                                            <span class="badge bg-warning text-dark">Dipinjam</span>
                                                        @elseif ($child->sedang_diperbaiki)
                                                            <span class="badge bg-danger">Diperbaiki</span>
                                                        @else
                                                            <span class="badge bg-success">Tersedia</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <x-tombol-aksi href="{{ route('barang.show', $child->id) }}" type="show" />
                                                            <x-tombol-aksi href="{{ route('barang.edit', $child->id) }}" type="edit" />
                                                            <x-tombol-aksi href="{{ route('barang.destroy', $child->id) }}" type="delete" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        Total <strong>{{ $barang->childUnits->count() }}</strong> unit anak dengan kode dasar: 
                                        <code>{{ $barang->kode_barang }}</code>
                                    </small>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox"></i><br>
                    Belum ada data barang.
                </td>
            </tr>
        @endif
    </x-table-list>
</div>

{{-- AlpineJS --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    [x-cloak] { display: none !important; }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
