@extends('layouts.dashboard')

@section('content')

    <div class="row position-relative">
        <div style="height: 85vh; width: 100vw" id="map">
        </div>
        <div class="map-header-menu">
            <h2 id="__car_name"></h2>
            <button class="btn btn-light" type="button"
                    style="position: absolute; top: 0.5rem; right: 12rem;"
                    data-target="drawAllCars">
                {{ __('dashboard.cars.all cars') }}
            </button>
            <button class="btn btn-light dropdown-toggle" type="button"
                    style="position: absolute; top: 0.5rem; right: 4rem;"
                    id="mapDropDownMenuButton">{{ __('dashboard.cars.cars') }}
            </button>
            <ul class="list-unstyled map-menu" dropdown="hide" data-units-km="{{ __('dashboard.map.km') }}">
                @foreach($cars as $car)
                    <li class="map-menu-item">
                        <a class="map-menu-item__car_link py-3 px-3 bg-light card-link text-dark d-block position-relative"
                           data-toggle="collapse"
                           href="#car-{{ $car->id }}"
                           aria-expanded="false"><span>{{ $car->name_full }} {{ $car->gov_number }}</span>
                            <img class="position-absolute" src="{{ $car->brand->image }}" width="100px"
                                 style="right: 0; top: 0" alt="brand-logo">
                        </a>
                        <map-car style="display: none"
                                 data-car-id="{{ $car->id }}"
                                 data-car-name="{{ $car->name_full }}"
                                 @if(auth()->user()->settings->where('setting_id', 1)->first()->value)
                                 data-car-point-image="{{ $car->image }}"
                                 @else
                                 data-car-point-image="{{ asset('images/map/car-point.png') }}"
                                 @endif
                                 data-car-gov-number="{{ $car->gov_number }}"
                                 data-car-gov-number-translate="{{ __('dashboard.cars.table.gov_number') }}"
                                 data-car-location-latitude="{{ $car->location->latitude }}"
                                 data-car-location-longitude="{{ $car->location->longitude }}">
                        </map-car>

                        <div class="collapse bg-light rounded" id="car-{{ $car->id }}"
                             data-target="map-menu-collapse-car-block">
                            <ol class="p-0">
                                <div class="d-flex justify-content-between bg-primary text-center rounded-0">
                                    <div class="w-100 py-2 px-1"
                                         style="border-top-left-radius: 5px">
                                        {{ __('dashboard.map.time on the way') }}:
                                    </div>
                                    <div class="w-100 py-2 px-1"
                                         style="border-top-right-radius: 5px">
                                        {{ $car->moving_time }}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-success rounded-0 w-100 py-2 px-1 text-center"
                                            name="map-set-center-button"
                                            data-latitude="{{ $car->location->latitude }}"
                                            data-longitude="{{ $car->location->longitude }}">
                                        {{ __('dashboard.cars.table.location') }}
                                    </button>
                                    <div class="bg-dark w-100 py-2 px-1 text-center">
                                        Груз
                                    </div>
                                </div>
                                @foreach ($car->routes->reverse() as $route)
                                    <li class="map-menu-item">
                                        <a class="map-menu-item__route_link mb-2"
                                           data-toggle="collapse"
                                           href="#route-{{ $route->id }}"
                                           data-route-id="{{ $route->id }}"
                                           aria-expanded="false">
                                            {{ $route->name }}
                                            <span class="badge bg-gradient-primary text-white"
                                                  name="map_route_length-{{ $route->id }}">

                                            </span>
                                            @if($car->isCurrentRoute($route->id))
                                                <span class="badge bg-gradient-success text-white" data-route="current">
                                                    {{ __('dashboard.map.current route') }}
                                                </span>
                                            @endif
                                        </a>
                                        <div class="collapse" id="route-{{ $route->id }}">
                                            <div class="card card-body bg-dark border-dark">
                                                <p class="text-center">{{ $route->moving_time}}</p>
                                                <div class="row mt-2">
                                                    <div class="col-6">
                                                        <button class="btn btn-outline-light w-100" type="button"
                                                                name="map-set-center-button"
                                                                data-latitude="{{ $route->start->latitude }}"
                                                                data-longitude="{{ $route->start->longitude }}">
                                                            {{ __('dashboard.map.start route') }}
                                                        </button>
                                                    </div>
                                                    <div class="col-6">
                                                        <button class="btn btn-outline-light w-100" type="button"
                                                                name="map-set-center-button"
                                                                data-latitude="{{ $route->end->latitude }}"
                                                                data-longitude="{{ $route->end->longitude }}">
                                                            {{ __('dashboard.map.end route') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <map-sections style="display: none" data-route-id="{{ $route->id }}">
                                            @foreach($route->sections as $section)
                                                <section
                                                        data-id="{{ $section->id }}"
                                                        data-start-point-latitude="{{ $section->start_point->latitude }}"
                                                        data-start-point-longitude="{{ $section->start_point->longitude }}"
                                                        data-end-point-latitude="{{ $section->end_point->latitude }}"
                                                        data-end-point-longitude="{{ $section->end_point->longitude }}"
                                                        data-moving-time="{{ $section->moving_time }}">
                                                </section>
                                            @endforeach
                                        </map-sections>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection

<style>
    .map-header-menu {
        position: absolute;
        top: -1.45rem;
        margin-left: 0;
        width: 100%;
        height: 50px;
        background-color: rgba(0, 0, 0, 0.36);
    }

    .map-header-menu #__car_name {
        margin-left: 40px;
        color: white;
    }

    .map-menu {
        color: white;
        position: absolute;
        overflow: auto;
        top: 3.1rem;
        right: 2rem;
        width: 450px;
        height: 0px;
        background-color: rgba(0, 0, 0, 0.36);
    }

    .map-menu .map-menu-item {
        padding: 5px;
    }
</style>

@section('scripts')
    @php($mapLink = 'https://api-maps.yandex.ru/2.1/?apikey=' . env('YANDEX_MAP_API_KEY') . '&lang=' . app()->getLocale() . '_RU')
    <script src="{{ $mapLink }}"></script>
    <script src="{{ mix('admin/js/map.js') }}"></script>
@endsection

