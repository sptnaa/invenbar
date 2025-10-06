<x-table-list>
    <x-slot name="header">
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Lokasi Penugasan</th>
            <th>Aksi</th>
        </tr>
    </x-slot>

    @forelse ($users as $index => $user)
        <tr>
            <td>{{ $users->firstItem() + $index }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <span class="badge bg-primary">{{ $user->getRoleNames()->first() }}</span>
            </td>
            <td>
                @if($user->lokasi)
                    <span class="badge bg-info">{{ $user->lokasi->nama_lokasi }}</span>
                @else
                    <span class="badge bg-secondary">Semua Lokasi</span>
                @endif
            </td>
            <td>
                <x-tombol-aksi :href="route('user.edit', $user->id)" type="edit" />
                <x-tombol-aksi :href="route('user.destroy', $user->id)" type="delete" />
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center">
                <div class="alert alert-danger">
                    Data user belum tersedia.
                </div>
            </td>
        </tr>
    @endforelse
</x-table-list>