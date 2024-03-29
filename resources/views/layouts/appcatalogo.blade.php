<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/style-datatables.css') }}" rel="stylesheet" id="bootstrap-css">
    <title>{{ config('app.name', 'esClothing') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-inter antialiased bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400">


    <!-- Page wrapper -->
    <div class="flex h-screen overflow-hidden">


        <!-- Content area -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden" x-ref="contentarea">
            <x-app.headerstore />

            <main>

                @yield('content')

            </main>

        </div>

    </div>

    @livewireScripts
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('scripts')
</body>

</html>
