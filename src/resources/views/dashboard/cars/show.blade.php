@extends('layouts.dashboard')

@section('content')
    @include('dashboard.breadcrumbs')
    <div class="row">
        <div class="col-10 offset-1">
            <div class="bg-white shadow p-3">
                <div class="row">
                    <div class="col-4">
                        <img src="{{ $car->image }}" class="w-100" alt="car image" style="object-fit: cover;">
                    </div>
                    <div class="col-8">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.name') }}
                            </div>
                            <div class="col-9">
                                {{ $car->name }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.brand_name') }}
                            </div>
                            <div class="col-9">
                                {{ $car->brand->name }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.api_key') }}
                            </div>
                            <div class="col-9">
                                {{ $car->api_code }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.year') }}
                            </div>
                            <div class="col-9">
                                {{ $car->year }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.vin_number') }}
                            </div>
                            <div class="col-9">
                                {{ $car->vin_number }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.gov_number') }}
                            </div>
                            <div class="col-9">
                                {{ $car->gov_number }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.color') }}
                            </div>
                            <div class="col-9 shadow-sm" style="background-color: {{ $car->color }}">
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                        <div class="row p-0 m-0">
                            <div class="col-3">
                                {{ __('dashboard.cars.table.description') }}
                            </div>
                            <div class="col-9">
                                {{ $car->description }}
                            </div>
                        </div>
                        <hr class="p-0 m-1">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
