<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="{{ mix('admin/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ mix('admin/css/sb-admin-2.css') }}">

</head>

<body id="page-top">

<div id="wrapper">

@include('dashboard.sidebar')

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            @include('navigation.dashboard_navigation')

            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('auth.ready to leave') }}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">{{ __('auth.logout ready') }}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">{{ __('dashboard.general.forms.cancel') }}</button>
                <form action="{{ route('logout', app()->getLocale()) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">{{ __('auth.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ mix('admin/js/app.js') }}"></script>
<script src="{{ mix('admin/js/sweetalert.js') }}"></script>
@include('sweetalert::alert', ['cdn' => ''])
@yield('scripts')

</body>
</html>
