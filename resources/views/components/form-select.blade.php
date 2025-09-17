@props([
    'name',
    'label' => null,
    'value' => null,
    'disabled' => false,
    'optionData' => null,
    'optionValue' => null,
    'optionLabel' => null,
])

@if ($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
    </label>
@endif

@php
    $value = old($name, $value ?? '');
@endphp

<select name="{{ $name }}" id="{{ $name }}" 
        class="form-select @error($name) is-invalid @enderror"
        @disabled($disabled)>
        
    <option value="">Pilih {{ $label }} :</option>

    @foreach ($optionData as $item)
        <option value="{{ $item[$optionValue] }}" 
            @if ($value == $item[$optionValue]) selected @endif>
            {{ $item[$optionLabel] }}
        </option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
