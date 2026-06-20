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

    function drawAllCars() {
        hideCollapseBlocksInMapMenu(carMapMenuCollapses)
        var routesHTML = document.querySelectorAll('map-sections'),
            carsHTML = document.querySelectorAll('map-car'),
            carsMAP = new ymaps.GeoObjectCollection(null),
            routesMAP = new ymaps.GeoObjectCollection(null)

        clearMap(myMap)
        clearCollection(carsMAP)
        clearCollection(routesMAP)

        carsHTML.forEach(function (carHTML) {

            // creating cars collection
            carsMAP.add(factoryCarPoints(getCar(carHTML)));
        })

        routesHTML.forEach(function (routeHTML) {
            let routeID = routeHTML.getAttribute('data-route-id'),
                routeLength = 0,
                sectionsHTML = routeHTML.getElementsByTagName('section'),
                isCurrentRoute = routeHTML.parentNode.querySelector('span[data-route=current]') !== null

            for (sectionHTML of sectionsHTML) {
                let section = getRouteSection(sectionHTML)

                // display only the current route
                if (isCurrentRoute) {
                    routesMAP.add(factoryRouteSections(section))
                }

                routeLength = Number(routeLength) + Number(section.distance.length);
            }

            let routeLengthElement = document.getElementsByName('map_route_length-' + routeID)
            routeLengthElement[0].textContent = routeLength.toFixed(1) + ' ' + unitsIsmKM
        })

        myMap.geoObjects
            .add(routesMAP)
            .add(carsMAP)

        myMap.setBounds(carsMAP.getBounds());
    }

    drawAllCars()
    ////////////////////////////////////////////////////////////////////////////////
    // functions
    ////////////////////////////////////////////////////////////////////////////////
    function getCar(carHTML) {
        return {
            ID: carHTML.getAttribute('data-car-id'),
            name: carHTML.getAttribute('data-car-name'),
            govNumber: carHTML.getAttribute('data-car-gov-number'),
            govNumberWord: carHTML.getAttribute('data-car-gov-number-translate'),
            image: carHTML.getAttribute('data-car-point-image'),
            location: {
                latitude: carHTML.getAttribute('data-car-location-latitude'),
                longitude: carHTML.getAttribute('data-car-location-longitude')
            }
        }
    }

    function getRouteSection(sectionHTML) {
        let section = {
            sectionID: sectionHTML.getAttribute('data-id'),
            movingTime: sectionHTML.getAttribute('data-moving-time'),
            startPoint: [
                sectionHTML.getAttribute('data-start-point-latitude'),
                sectionHTML.getAttribute('data-start-point-longitude')
            ],
            endPoint: [
                sectionHTML.getAttribute('data-end-point-latitude'),
                sectionHTML.getAttribute('data-end-point-longitude')
            ]
        }

        let distance = ymaps.formatter.distance(
            ymaps.coordSystem.geo.getDistance(section.startPoint, section.endPoint)
        )

        section.distance = {
            length: distance.match('(^.*)&#')[1],
            valueName: distance.match(';(.*$)')[1]
        }

        if (section.distance.valueName === 'm' || section.distance.valueName === 'м') {
            section.distance.length = section.distance.length / 1000
        }

        return section
    }

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

    jQuery('[name=map-set-center-button]').on('click', function (event) {
        let coords = [
            event.target.getAttribute('data-latitude'),
            event.target.getAttribute('data-longitude')
        ]

        setCenter(coords)
    });

    jQuery('button[data-target="drawAllCars"]').on('click', function () {
        drawAllCars()
    })


    carMapMenuLink.forEach(link => {
        let parent = link.parentNode,
            routeMAP = new ymaps.GeoObjectCollection(null),
            carMAP = new ymaps.GeoObjectCollection(null)

        // click on car link
        link.onclick = function (event) {
            console.log('click on car')

            let carInfo = parent.querySelector('map-car'),
                car = getCar(carInfo),
                routes = parent.querySelectorAll('a.map-menu-item__route_link')

            // creating car location point
            clearCollection(carMAP)
            carMAP.add(factoryCarPoints(car));
            setCenter([car.location.latitude, car.location.longitude])

            routes.forEach(routeLink => {
                // click on route link
                routeLink.onclick = function (event) {
                    console.log('click on route')
                    let link = event.target,
                        routeID = link.getAttribute('data-route-id'),
                        route = parent.querySelector('map-sections[data-route-id="' + routeID + '"]'),
                        routeSections = route.querySelectorAll('section');

                    clearCollection(routeMAP)

                    routeSections.forEach(routeSection => {
                        let section = getRouteSection(routeSection)

                        routeMAP.add(factoryRouteSections(section))
                    })

                    myMap.setBounds(routeMAP.getBounds());
                }
            })

            // set car name on header in menu map
            document.getElementById('__car_name').textContent = car.name + ' ' + car.govNumber

            hideCollapseBlocksInMapMenu(carMapMenuCollapses)

            clearMap(myMap)
            myMap.geoObjects
                .add(routeMAP)
                .add(carMAP)
        }
    })


    function clearMap(map) {
        map.geoObjects.removeAll();
    }

    function clearCollection(collection) {
        collection.removeAll()
    }

    function hideCollapseBlocksInMapMenu(carMapMenuCollapses) {
        carMapMenuCollapses.forEach(collapseBlock => {
            let collapseBlocks = document.querySelectorAll('div[data-target=map-menu-collapse-car-block]')

            collapseBlocks.forEach(block => {
                if (block.classList.contains('show')) {
                    collapseBlock.classList.remove('show')
                }
            })
        })
    }
}
