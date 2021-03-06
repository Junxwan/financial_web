<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css?_=' . $time) }}">
    <link rel="stylesheet" href="{{ asset('css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        .container-fluid .card-header {
            background-color: #888f95;
            color: #0c0c0c;
        }

        .dark-mode input {
            text-align: center;
        }

        th {
            text-align: center;
        }

        td {
            text-align: center;
        }
    </style>

    @yield('css')
</head>

<body class="dark-mode sidebar-mini layout-navbar-fixed sidebar-collapse" style="height: auto;">
@yield('body')

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('js/adminlte.min.js') }}"></script>
<script src="{{ asset('js/toastr.min.js') }}"></script>

@yield('js')
</body>
</html>
