@props(['label', 'href'])
<a href="{{ $href }}" class="btn btn-success" target="_blank">
    <i class="bi bi-printer">
        {{ $label }}
    </i>
</a>