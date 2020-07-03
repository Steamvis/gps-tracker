@extends('layouts.dashboard')

@section('content')
    <div class="row position-relative">
        <div style="height: 85vh; width: 100vw" id="map">
        </div>
        <div class="map-header-menu">
            <h2 id="__car_name">s</h2>
            <button class="btn btn-light dropdown-toggle" type="button"
                    id="mapDropDownMenuButton">
                Маршруты
            </button>
            <ul class="list-unstyled map-menu" dropdown="hide">
                @foreach($cars as $car)
                    <map-car style="display: none"
                             data-car-id="{{ $car->id }}"
                             data-car-name="{{ $car->name_full }}"
                             data-car-point-image="{{ asset('images/map/car-point.png') }}"
                             data-car-gov-number="{{ $car->gov_number }}"
                             data-car-gov-number-translate="{{ __('dashboard.cars.table.gov_number') }}"
                             data-car-location-latitude="{{ $car->location->latitude }}"
                             data-car-location-longitude="{{ $car->location->longitude }}">
                    </map-car>
                    @foreach ($car->routes as $route)
                        <li class="map-menu-item">
                            <a class="map-menu-item__route_link"
                               data-toggle="collapse"
                               href="#route-{{ $route->id }}"
                               aria-expanded="false">
                                {{ $route->name }}
                            </a>
                            <div class="collapse" id="route-{{ $route->id }}">
                                <div class="card card-body bg-dark border-dark">
                                    <p>{{ $route->moving_time}}</p>
                                    <div class="row">
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

    .map-header-menu #mapDropDownMenuButton {
        position: absolute;
        top: 0.5rem;
        right: 4rem;
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

    .map-menu .map-menu-item__route_link {
        color: white;
    }

    .map-menu .map-menu-item__route_link:hover {
        color: white;
    }

</style>

@section('scripts')
    @php($mapLink = 'https://api-maps.yandex.ru/2.1/?apikey=' . env('YANDEX_MAP_API_KEY') . '&lang=' . app()->getLocale() . '_RU')
    <script src="{{ $mapLink }}"></script>
    <script>
        jQuery('#mapDropDownMenuButton').on('click', function (event) {
            let menu = jQuery('.map-menu');

            if (menu.attr('dropdown') === 'hide') {
                menu.attr('dropdown', 'visible')
                menu.animate({
                    height: '500px',
                })
            } else {
                menu.attr('dropdown', 'hide').animate({
                    height: 0,
                })
            }
        })
    </script>
    <script>
        ymaps.ready(init);

        function init() {
            let myMap = new ymaps.Map("map", {
                center: [55.76, 37.64],
                zoom: 7,
                controls: []
            })

            let myIconContentLayout = ymaps.templateLayoutFactory.createClass(
                '<div style="color: #FFFFFF; font-weight: bold;">$[properties.iconContent]</div>'
            );

            let routesHTML = document.querySelectorAll('map-sections'),
                routesMAP = new ymaps.GeoObjectCollection(null),
                carsHTML = document.querySelectorAll('map-car'),
                carsMAP = new ymaps.GeoObjectCollection(null);

            carsHTML.forEach(function (carHTML) {
                let ID = carHTML.getAttribute('data-car-id'),
                    name = carHTML.getAttribute('data-car-name'),
                    govNumber = carHTML.getAttribute('data-car-gov-number'),
                    translate = carHTML.getAttribute('data-car-gov-number-translate'),
                    image = carHTML.getAttribute('data-car-point-image'),
                    location = {
                        latitude: carHTML.getAttribute('data-car-location-latitude'),
                        longitude: carHTML.getAttribute('data-car-location-longitude')
                    };


                carsMAP.add(new ymaps.Placemark(
                    [location.latitude, location.longitude],
                    {
                        balloonContent: '<strong>' + name + '</strong><br>' + translate + ': ' + govNumber + ''
                    },
                    {
                        iconLayout: 'default#image',
                        iconImageSize: [50, 50],
                        iconImageOffset: [-10, -30],
                        iconImageHref: image,
                    }
                ))
            });

            routesHTML.forEach(function (routeHTML) {
                let routeID = routeHTML.getAttribute('data-route-id'),
                    sectionsHTML = routeHTML.getElementsByTagName('section');

                for (sectionHTML of sectionsHTML) {
                    let sectionID = sectionHTML.getAttribute('data-id'),
                        movingTime = sectionHTML.getAttribute('data-moving-time'),
                        startPoint = {
                            latitude: sectionHTML.getAttribute('data-start-point-latitude'),
                            longitude: sectionHTML.getAttribute('data-start-point-longitude')
                        },
                        endPoint = {
                            latitude: sectionHTML.getAttribute('data-end-point-latitude'),
                            longitude: sectionHTML.getAttribute('data-end-point-longitude')
                        };


                    routesMAP.add(new ymaps.Polyline([
                        [startPoint.latitude, startPoint.longitude],
                        [endPoint.latitude, endPoint.longitude],
                    ], {
                        balloonContentHeader: "",
                        balloonContent: movingTime
                    }, {
                        balloonCloseButton: false,
                        strokeColor: "#2c56c1",
                        strokeWidth: 6,
                        strokeOpacity: .6
                    }))
                }
            })

            myMap.geoObjects
                .add(routesMAP)
                .add(carsMAP)

            // functions
            function setCenter(coords) {
                myMap.setCenter(coords);
            }

            let mapSetCenterButtons = jQuery('[name=map-set-center-button]');

            mapSetCenterButtons.on('click', function (event) {
                let coords = [
                    event.target.getAttribute('data-latitude'),
                    event.target.getAttribute('data-longitude')
                ]

                setCenter(coords)
            })

        }
    </script>
@endsection
