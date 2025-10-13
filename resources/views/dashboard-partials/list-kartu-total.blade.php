<div class="row">
    @php
    $kartus = [
    [
    'text' => 'TOTAL BARANG',
    'total' => $jumlahBarang,
    'route' => 'barang.index',
    'icon' => 'bi-box-seam',
    'color' => 'primary',
    ],
    [
    'text' => 'TOTAL KATEGORI',
    'total' => $jumlahKategori,
    'route' => 'kategori.index',
    'icon' => 'bi-tag',
    'color' => 'secondary',
    ],
    [
    'text' => 'TOTAL LOKASI',
    'total' => $jumlahLokasi,
    'route' => 'lokasi.index',
    'icon' => 'bi-geo-alt',
    'color' => 'success',
    'role' => 'admin',
    ],
    [
    'text' => 'TOTAL PEMINJAMAN',
    'total' => $totalPeminjaman,
    'route' => 'peminjaman.index',
    'icon' => 'bi-journal-check',
    'color' => 'info',
    ],
    [
    'text' => 'TOTAL PERBAIKAN',
    'total' => $totalPerbaikan,
    'route' => 'perbaikan.index',
    'icon' => 'bi-tools',
    'color' => 'warning',
    ],
    [
    'text' => 'TOTAL USER',
    'total' => $jumlahUser,
    'route' => 'user.index',
    'icon' => 'bi-people',
    'color' => 'danger',
    'role' => 'admin',
    ],

    ];
    @endphp

   @foreach ($kartus as $kartu)
    @php
        extract($kartu);
        unset($role); //
    @endphp

    @isset($kartu['role'])
        @if(auth()->user()->hasRole($kartu['role']))
            <x-kartu-total :text="$text" :route="$route" :total="$total" :icon="$icon" :color="$color" />
        @endif
    @else
        <x-kartu-total :text="$text" :route="$route" :total="$total" :icon="$icon" :color="$color" />
    @endisset
@endforeach
</div>