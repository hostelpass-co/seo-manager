<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/dkrasov/fonts/feather/feather.min.css') }}">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/dkrasov/css/theme.min.css') }}">

    <script>
        let API_URL = '{{ route('seo-manager.home') }}';
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Seo Manager</title>
</head>
<body>

<div id="lionix-seo-manager-app">
    {{--Main Content--}}
    <app></app>
</div>


<!-- JAVASCRIPT
================================================== -->
<!-- Libs JS -->
<script src="{{ asset('vendor/dkrasov/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/dkrasov/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('vendor/dkrasov/js/seo-manager.app.js') }}"></script>
</body>

</html>
