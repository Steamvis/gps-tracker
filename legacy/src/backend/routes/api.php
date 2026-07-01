<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// example.test/api/v1/gps/51/52/API_CODE/1_0 start route
// example.test/api/v1/gps/51/52/API_CODE/0_0 moves on the route
// example.test/api/v1/gps/51/52/API_CODE/0_1 end route

Route::middleware('api')->prefix('gps')->group(function () {
    Route::post('/{latitude}/{longitude}/{carInfo}/{start_route}_{end_route}', 'Api\MapController@routeGenerator')
        ->name('api.gps')
        ->where([
            'latitude' => '([0-9]+)\.?([0-9]+)?',
            'longitude' => '([0-9]+)\.?([0-9]+)?',
            'carInfo' => '^[0-9a-z]{10}\_[0-9]+$',
            'start_route' => '^(0|1)$',
            'end_route' => '^(0|1)$',
        ]);
});
