@php
    $message = session('success') ?? session('error');
    $type = session('success') ? 'success' : 'danger';
@endphp

@if ($message)
    <div {{ $attributes->merge([
            'class' => "alert alert-$type alert-dismissible fade show",
            'role' => 'alert',
        ]) }}>
        {{ $message }}

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
