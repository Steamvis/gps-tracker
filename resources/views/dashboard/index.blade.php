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

    .map-menu a {
        color: white;
    }

</style>

@section('scripts')
    @php($mapLink = 'https://api-maps.yandex.ru/2.1/?apikey=' . env('YANDEX_MAP_API_KEY') . '&lang=' . app()->getLocale() . '_RU')
    <script src="{{ $mapLink }}"></script>

    <script>
        ymaps.ready(init);

        function init() {
            let myMap = new ymaps.Map("map", {
                    center: [55.76, 37.64],
                    zoom: 7,
                    controls: []
                }),
                routes = [],
                menu = jQuery('<ol class="map-menu"  dropdown="hide"></ol>');

                @foreach($routes as $route)
                {{--                @dd($route->start_point->id, $route->end_point->id)--}}
                {{--let point{{ $route->start_point->id }} = new ymaps.Placemark([--}}
                {{--        {{ $route->start_point->id }}--}}
                {{--    ], {--}}
                {{--    hintContent: 'Собственный значок метки с контентом',--}}
                {{--    balloonContent: 'А эта — новогодняя',--}}
                {{--    iconContent: ''--}}
                {{--});--}}

                {{--myMap.geoObjects.add(point{{ $route->start_point->id }});--}}

            let route{{ $route->id }} = new ymaps.GeoObjectCollection(null);

            route{{ $route->id }}.add(new ymaps.Polyline([
                [{{ $route->start_point->latitude }}, {{ $route->start_point->longitude }}],
                [{{ $route->end_point->latitude }}, {{ $route->end_point->longitude }}]
            ], {
                // balloonContentHeader: "test",
                balloonContentHeader: "{{"{$route->start_point->car->brand->name} {$route->start_point->car->name} {$route->start_point->car->gov_number}"}}",
                balloonContent: "{{ $route->moving_time }}"
            }, {
                balloonCloseButton: false,
                strokeColor: "#2c56c1",
                strokeWidth: 6,
                strokeOpacity: .6
            }))

            myMap.geoObjects.add(route{{ $route->id }});
            routes.push(route{{ $route->id }})

            @endforeach

            function createMenuGroup(coords, name = 'test') {
                let menuItem = jQuery(
                    '<li>' +
                    '<a href="#" name="__map-route__menu-link"' +
                    'data-latitude="' + coords.latitude + '" ' +
                    'data-longitude="' + coords.longitude + '"' +
                    '>' + name + '</a>' +
                    '</li>'
                );

                menuItem
                    .appendTo(menu)
                    .find('a');
            }


            routes.forEach(function (geoObjectCollection) {

                geoObjectCollection.each(function (geoObject) {
                    let data = geoObject.properties._data,
                        bounds = geoObjectCollection.getBounds(),
                        coords = {
                            latitude: bounds[1][0],
                            longitude: bounds[1][1],
                        },
                        name = data.balloonContent

                    createMenuGroup(coords, name)
                })
            });

            menu.appendTo(jQuery('.map-header-menu'));

оли            function setCenter(coords, map) {
                map.setCenter(coords, 15);
            }

            jQuery('[name=__map-route__menu-link]').on('click', function (event) {
                event.preventDefault();
                let button = event.target,
                    latitude = button.getAttribute('data-latitude'),
                    longitude = button.getAttribute('data-longitude'),
                    coords = [latitude, longitude];

                setCenter(coords, myMap);
            })

            jQuery('#mapDropDownMenuButton').on('click', function (event) {
                let menu = jQuery('.map-menu');

                if (menu.attr('dropdown') === 'hide') {
                    menu.attr('dropdown', 'visible')
                    menu.animate({
                        height: '500px',
                        paddingTop: '20px',
                        paddingleft: '50px'
                    })
                } else {
                    menu.attr('dropdown', 'hide').animate({
                        height: 0,
                        paddingTop: '0',
                        paddingleft: '0'
                    })
                }
            })
        }
    </script>
@endsection
