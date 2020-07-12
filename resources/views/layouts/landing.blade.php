<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grayscale - Start Bootstrap Theme</title>
    <link href="{{ mix('landing/css/app.css') }}" rel="stylesheet">
</head>
<body>
@include('navigation.landing_navigation')

@if($errors->any())
    @dd($errors)
@endif


<header class="masthead">
    <div class="container vertical-center">
        <div class="row w-100">
            <div class="col-6 mx-auto">
                @yield('content')
            </div>
        </div>
    </div>
</header>

<script src="{{ mix('js/app.js') }}" defer></script>
</body>
</html>
