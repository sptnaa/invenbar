<x-main-layout :title-page="__('Edit Data Perbaikan')">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('perbaikan.update', $perbaikan->id) }}" method="POST">
                @method('PUT')
                @php $update = true; @endphp
                @include('perbaikan.partials._form')
            </form>
        </div>
    </div>
</x-main-layout>