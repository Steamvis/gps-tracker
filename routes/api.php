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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('api')->prefix('gps')->group(function () {
    Route::get('/{latitude}/{longitude}/{carInfo}/{start_route}_{end_route}', 'Api\ApiController@updatePoint')
        ->name('api.gps')->where([
            'latitude'  => '([0-9]+)\.?([0-9]+)?',
            'longitude' => '([0-9]+)\.?([0-9]+)?',
            'carInfo' => '^[0-9a-z]{10}\_[0-9]+$',
            'start_route' => '^(0|1)$',
            'end_route' => '^(0|1)$',
        ]);
});
