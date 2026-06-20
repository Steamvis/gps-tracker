<?php

namespace App\Http\Controllers\Api;

use App\DTO\MapDTO;
use App\Helpers\Map\RouteGenerator;
use App\Http\Controllers\Controller;
use App\Models\Car\Car;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function routeGenerator(Request $request)
    {
        [$apiCode, $carID] = explode('_', $request->carInfo);

        $isStartRoute = $request->start_route ? true : false;
        $isEndRoute = $request->end_route ? true : false;
        $car = Car::find($carID);

        app(RouteGenerator::class)->generate(
            new MapDTO(
                $car,
                $apiCode,
                $request->latitude,
                $request->longitude,
                $isStartRoute,
                $isEndRoute
            )
        );

        return response()->json()->getStatusCode();
    }
}
