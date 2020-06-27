@extends('layouts.landing')

@section('content')
    <div class="container d-flex h-100 align-items-center">
        <div class="mx-auto text-center">
            <h1 class="mx-auto my-0 text-uppercase">{{ config('app.name', 'crm') }}</h1>
            <h2 class="text-white-50 mx-auto mt-2 mb-5">
                A free, responsive, one page Bootstrap theme created by Start Bootstrap.
            </h2>
            <a class="btn btn-secondary btn-lg" href="#about">Get Started</a>
        </div>
    </div>
@endsection
