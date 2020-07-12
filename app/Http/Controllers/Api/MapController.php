<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Map\RouteGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function routeGenerator(Request $request)
    {
        $routeGenerator = new RouteGenerator($request);

        $routeGenerator->generate();

        return response()->json()->getStatusCode();
    }
}
