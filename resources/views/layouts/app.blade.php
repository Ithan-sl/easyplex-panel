<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>


    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">


    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}"/>

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}?v={{ file_exists(public_path('js/manifest.js')) ? filemtime(public_path('js/manifest.js')) : time() }}"></script>   
    <script src="{{ asset('js/vendor.js') }}?v={{ file_exists(public_path('js/vendor.js')) ? filemtime(public_path('js/vendor.js')) : time() }}"></script>     
    <script src="{{ asset('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}"></script>   


</head>
<body>

@yield('content')

</body>
</html>
