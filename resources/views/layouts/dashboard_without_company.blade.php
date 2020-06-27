<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="{{ mix('admin/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ mix('admin/css/sb-admin-2.css') }}">

</head>

<body class="bg-gradient-primary">

<div class="container">
    @yield('content')
</div>

<script src="{{ mix('admin/js/app.js') }}"></script>
</body>
</html>
