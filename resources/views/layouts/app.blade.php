<!DOCTYPE html>
<html lang="en">

<head>
    <x-head />
    <title>Homepage | ADMIN DASHBOARD</title>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <main id="main-wrapper" class="main-wrapper">
        <x-header />
        <x-navbar-vertical />
        <!-- Page content -->
        <div id="app-content">
            <div class="app-content-area">
                    @yield('content')
            </div>
        </div>
    </main>




    <x-script />
</body>
</html>
