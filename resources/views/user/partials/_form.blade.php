@csrf
<div class="mb-3">
    <x-form-input label="Nama Lengkap" name="name" :value="$user->name" />
</div>

<div class="mb-3">
    <x-form-input label="Email" name="email" :value="$user->email" type="email" />
</div>

<div class="mb-3">
    <label for="lokasi_id" class="form-label">Lokasi Penugasan</label>
    <select class="form-select @error('lokasi_id') is-invalid @enderror" id="lokasi_id" name="lokasi_id">
        <option value="">-- Pilih Lokasi --</option>
        @foreach($lokasis as $lokasi)
            <option value="{{ $lokasi->id }}" {{ old('lokasi_id', $user->lokasi_id) == $lokasi->id ? 'selected' : '' }}>
                {{ $lokasi->nama_lokasi }}
            </option>
        @endforeach
    </select>
    @error('lokasi_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Petugas hanya dapat mengakses barang di lokasi yang ditugaskan</small>
</div>

<div class="mb-3">
    <x-form-input label="Password" name="password" type="password" />
    @if(isset($update))
        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
    @endif
</div>

<div class="mb-3">
    <x-form-input label="Konfirmasi Password" name="password_confirmation" type="password" />
</div>

<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>

    <x-tombol-kembali :href="route('user.index')" />
</div>