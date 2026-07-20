<!DOCTYPE html>
<html lang="en">
<style>

    #sidebar{
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        transition: all 0.3s;
        overflow-x: hidden;
        z-index: 1000;
    }

    .sidebar-expanded{
        width: 260px;
    }

    .sidebar-collapsed{
        width: 80px;
    }

    .sidebar-collapsed .menu-text,
    .sidebar-collapsed .menu-title,
    .sidebar-collapsed .logo-text{
        display: none;
    }

    .sidebar-collapsed .nav-link{
        text-align: center;
    }

    .sidebar-collapsed .nav-link i{
        margin-right: 0 !important;
        font-size: 1.2rem;
    }

    #content{
        margin-left: 260px;
        transition: all 0.3s;
    }

    #content.expanded{
        margin-left: 80px;
    }

</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Konveksi Information System')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @stack('styles')
</head>
<body class="bg-light">

    <div class="d-flex">

        {{-- SIDEBAR --}}
        @include('layouts.sidebar')

        {{-- CONTENT --}}
        <div id="content"
            class="flex-grow-1"
            style="min-height:100vh;">

            <main class="p-4">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

    <script>

    document.addEventListener('DOMContentLoaded', function () {

        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        toggleBtn.addEventListener('click', function(){

            sidebar.classList.toggle('sidebar-expanded');
            sidebar.classList.toggle('sidebar-collapsed');

            content.classList.toggle('expanded');

        });

    });

</script>
</body>
</html>