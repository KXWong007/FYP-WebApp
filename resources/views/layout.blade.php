<!-- resources/views/layout.blade.php -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'A Renowned Club - Dashboard')</title>
    @yield('head')  <!-- Add this line -->
    @yield('styles')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (included as a partial) -->
        @include('partials.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header (included as a partial) -->
            @include('partials.header')

            <!-- Page-specific content -->
            <main class="container-fluid mt-3">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script src="{{ asset('js/reservation-dashboard.js') }}"></script>
</body>
</html>
