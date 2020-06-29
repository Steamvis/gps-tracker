<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect(route('landing', app()->getLocale())));

Route::group([
    'prefix'     => '{locale}',
    'middleware' => ['locale']
], function () {
    Auth::routes(['verify' => true]);

    Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
    Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

    Route::get('/', 'HomeController@index')->name('landing');
});

Route::group([
    'prefix'     => '{locale}',
    'middleware' => ['locale', 'verified']
], function () {
    Route::get('/register-company', 'Company\RegisterController@index')->name('company_register');
    Route::post('/register-company', 'Company\RegisterController@register')->name('company_register_create');
});

Route::group([
    'prefix'     => '{locale}/dashboard',
    'middleware' => ['locale', 'verified', 'company']
], function () {
    Alert::success('hello', 'text');
    Route::get('/', 'DashboardController@index')->name('dashboard.index');
    Route::resources([
        'cars' => 'Cars\CarsController',
    ]);
    Route::delete('destroy', 'Cars\CarsController@destroyMany')->name('cars.destroy.many');
});
