<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GPSRequest;
use App\Services\Map\Points\CreatePoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    public function index()
    {
    }

//    public function updatePoint(GPSRequest $request)
    public function updatePoint(Request $request)
    {
        $code = explode('_', $request->carInfo);

        $request->request->set('car_id', $code[1]);
        $request->request->set('api_code', $code[0]);
        $request->request->set('latitude', $request->latitude);
        $request->request->set('longitude', $request->longitude);

        app(CreatePoint::class)->execute($request->toArray());

        return response()->json()->getStatusCode();
    }
}
