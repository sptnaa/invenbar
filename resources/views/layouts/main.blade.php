{{-- resources/views/components/main-layout.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ ($titlePage ? $titlePage . ' - ' : '') . config('app.name', 'Laravel') }}</title>

    {{-- Bootstrap Icons --}}
    <link href="{{ asset('bootstrap/font/bootstrap-icons.min.css') }}" rel="stylesheet">

    {{-- Bootstrap CSS --}}
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Laravel Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-vh-100 bg-light pb-2">
        
        {{-- Navbar --}}
        @include('layouts.navigation')

        {{-- Page Header --}}
        @if ($titlePage)
            <header class="bg-white shadow-sm">
                <div class="container py-4">
                    <h2 class="h5 mb-0">
                        {{ $titlePage }}
                    </h2>
                </div>
            </header>
        @endif

        {{-- Main Content --}}
        <main class="container">
            <div class="my-5">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-modal-delete />

    {{-- Bootstrap JS --}}
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        const deleteModal = document.getElementById('deleteModal')

        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const url = button.getAttribute('data-url')
            const deleteForm = deleteModal.querySelector('form')
            deleteForm.setAttribute('action', url)
        });
    </script>
</body>
</html>
