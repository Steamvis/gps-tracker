@extends('layouts.dashboard')

@section('content')
    <div class="row position-relative">
        <div style="height: 85vh; width: 100vw" id="map">
        </div>
        <div class="map-header-menu">
            <h2 id="__car_name"></h2>
            <button class="btn btn-light dropdown-toggle" type="button"
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
                                 data-car-point-image="{{ asset('images/map/car-point.png') }}"
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
        let carMapMenuLink = document.querySelectorAll('a.map-menu-item__car_link'),
            carMapMenuCollapses = document.querySelectorAll('li.map-menu-item div[data-target=map-menu-collapse-car-block]'),
            unitsIsmKM = document.querySelector('ul.map-menu').getAttribute('data-units-km')

        ymaps.ready(init);

        function init() {
            var myMap = new ymaps.Map("map", {
                center: [55.76, 37.64],
                zoom: 7,
                controls: []
            })

            var routesHTML = document.querySelectorAll('map-sections'),
                carsHTML = document.querySelectorAll('map-car'),
                carsMAP = new ymaps.GeoObjectCollection(null),
                routesMAP = new ymaps.GeoObjectCollection(null)

            carsHTML.forEach(function (carHTML) {
                // creating cars collection
                carsMAP.add(factoryCarPoints({
                    ID: carHTML.getAttribute('data-car-id'),
                    name: carHTML.getAttribute('data-car-name'),
                    govNumber: carHTML.getAttribute('data-car-gov-number'),
                    govNumberWord: carHTML.getAttribute('data-car-gov-number-translate'),
                    image: carHTML.getAttribute('data-car-point-image'),
                    location: {
                        latitude: carHTML.getAttribute('data-car-location-latitude'),
                        longitude: carHTML.getAttribute('data-car-location-longitude')
                    }
                }));

                routesHTML.forEach(function (routeHTML) {
                    let routeID = routeHTML.getAttribute('data-route-id'),
                        routeLength = 0,
                        sectionsHTML = routeHTML.getElementsByTagName('section'),
                        isCurrentRoute = routeHTML.parentNode.querySelector('span[data-route=current]') !== null

                    for (sectionHTML of sectionsHTML) {
                        let sectionID = sectionHTML.getAttribute('data-id'),
                            movingTime = sectionHTML.getAttribute('data-moving-time'),
                            startPoint = [
                                sectionHTML.getAttribute('data-start-point-latitude'),
                                sectionHTML.getAttribute('data-start-point-longitude')
                            ],
                            endPoint = [
                                sectionHTML.getAttribute('data-end-point-latitude'),
                                sectionHTML.getAttribute('data-end-point-longitude')
                            ];

                        let distance = ymaps.formatter.distance(ymaps.coordSystem.geo.getDistance(startPoint, endPoint)),
                            length = '(^.*)&#',
                            valueName = ';(.*$)';

                        distance = {
                            length: distance.match(length)[1],
                            valueName: distance.match(valueName)[1]
                        }

                        if (distance.valueName === 'm' || distance.valueName === 'м') {
                            distance.length = distance.length / 1000
                        }

                        // display only the current route
                        if (isCurrentRoute) {
                            routesMAP.add(factoryRouteSections({
                                startPoint: startPoint,
                                endPoint: endPoint,
                                movingTime: movingTime,
                                distance: {
                                    length: distance.length,
                                    valueName: distance.valueName
                                }
                            }))
                        }

                        routeLength = Number(routeLength) + Number(distance.length);
                    }

                    let routeLengthElement = document.getElementsByName('map_route_length-' + routeID)
                    routeLengthElement[0].textContent = routeLength.toFixed(1) + ' ' + unitsIsmKM
                })


                myMap.geoObjects
                    .add(routesMAP)
                    .add(carsMAP)

                ////////////////////////////////////////////////////////////////////////////////
                // functions
                ////////////////////////////////////////////////////////////////////////////////

                function factoryCarPoints(car) {
                    return new ymaps.Placemark(
                        [car.location.latitude, car.location.longitude],
                        {
                            balloonContent: '<strong>' + car.name + '</strong><br>' + car.govNumberWord + ': ' + car.govNumber + ''
                        },
                        {
                            iconLayout: 'default#image',
                            iconImageSize: [50, 50],
                            iconImageOffset: [-10, -30],
                            iconImageHref: car.image,
                        }
                    )
                }

                function factoryRouteSections(section) {
                    return new ymaps.Polyline([
                        section.startPoint,
                        section.endPoint,
                    ], {
                        balloonContentHeader: section.movingTime,
                        balloonContent: section.distance.length + ' ' + section.distance.valueName
                    }, {
                        balloonCloseButton: false,
                        strokeColor: "#2c56c1",
                        strokeWidth: 6,
                        strokeOpacity: .6
                    })
                }

                function setCenter(coords) {
                    myMap.setCenter(coords, 17);
                }

                let mapSetCenterButtons = jQuery('[name=map-set-center-button]');

                mapSetCenterButtons.on('click', function (event) {
                    let coords = [
                        event.target.getAttribute('data-latitude'),
                        event.target.getAttribute('data-longitude')
                    ]

                    setCenter(coords)
                })

                carMapMenuLink.forEach(link => {
                    let parent = link.parentNode,
                        routeMAP = new ymaps.GeoObjectCollection(null),
                        carMAP = new ymaps.GeoObjectCollection(null)

                    // click on car link
                    link.onclick = function (event) {
                        console.log('click on car')

                        let carInfo = parent.querySelector('map-car'),
                            car = {
                                name: carInfo.getAttribute('data-car-name'),
                                id: carInfo.getAttribute('data-car-id'),
                                govNumber: carInfo.getAttribute('data-car-gov-number'),
                                govNumberWord: carInfo.getAttribute('data-car-gov-number-translate'),
                                image: carInfo.getAttribute('data-car-point-image'),
                                location: {
                                    latitude: carInfo.getAttribute('data-car-location-latitude'),
                                    longitude: carInfo.getAttribute('data-car-location-longitude')
                                }
                            },
                            routes = parent.querySelectorAll('a.map-menu-item__route_link')

                        // creating car location point
                        carMAP.add(factoryCarPoints(car));
                        setCenter([car.location.latitude, car.location.longitude])

                        routes.forEach(routeLink => {

                            // click on route link
                            routeLink.onclick = function (event) {
                                routeMAP.removeAll()
                                console.log('click on route')

                                let link = event.target,
                                    routeID = link.getAttribute('data-route-id'),
                                    route = parent.querySelector('map-sections[data-route-id="' + routeID + '"]'),
                                    routeSections = route.querySelectorAll('section');

                                routeSections.forEach(section => {
                                    let movingTime = section.getAttribute('data-moving-time'),
                                        startPoint = [
                                            section.getAttribute('data-start-point-latitude'),
                                            section.getAttribute('data-start-point-longitude')
                                        ],
                                        endPoint = [
                                            section.getAttribute('data-end-point-latitude'),
                                            section.getAttribute('data-end-point-longitude')
                                        ],
                                        distance = ymaps.formatter.distance(ymaps.coordSystem.geo.getDistance(startPoint, endPoint)),
                                        length = '(^.*)&#',
                                        valueName = ';(.*$)';

                                    distance = {
                                        length: distance.match(length)[1],
                                        valueName: distance.match(valueName)[1]
                                    }

                                    if (distance.valueName === 'm' || distance.valueName === 'м') {
                                        distance.length = distance.length / 1000
                                    }

                                    routeMAP.add(factoryRouteSections({
                                        startPoint: startPoint,
                                        endPoint: endPoint,
                                        movingTime: movingTime,
                                        distance: {
                                            length: distance.length,
                                            valueName: distance.valueName
                                        }
                                    }))
                                })
                            }

                            myMap.geoObjects.removeAll();
                        })

                        myMap.geoObjects
                            .add(routeMAP)
                            .add(carMAP)

                        function setCarNameOnMap() {
                            document.getElementById('__car_name').textContent = car.name + ' ' + car.number
                        }

                        setCarNameOnMap()

                        carMapMenuCollapses.forEach(collapseBlock => {
                            let currentCollapseBlock = parent.querySelector('div[data-target=map-menu-collapse-car-block]')

                            if (collapseBlock !== currentCollapseBlock) {
                                collapseBlock.classList.remove('show')
                            }
                        })
                    }
                })
            })
        }
    </script>
@endsection

