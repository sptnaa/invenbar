@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'disabled' => false])

@if ($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
    </label>
@endif

@php
    $value = $type === 'password' || $type === 'file' ? null : old($name, $value ?? '');
@endphp

<input type="{{ $type }}" class="form-control @error($name) is-invalid @enderror"
       id="{{ $name }}" name="{{ $name }}"
       value="{{ $value }}" @disabled($disabled)>

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
