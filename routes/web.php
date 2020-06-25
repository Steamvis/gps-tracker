<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect(route('home', app()->getLocale())));


Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');


//Route::match(['GET', 'HEAD'], '/password/reset/{token}')->name('password.reset');


Route::group([
    'prefix'     => '{locale}',
    'middleware' => ['locale']
], function () {
    Auth::routes(['verify' => true]);

    Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');

    Route::get('/', 'HomeController@index')->name('home');
});



Route::group([
    'prefix'     => '{locale}',
    'middleware' => ['locale', 'verified']
], function () {
    Route::get('test', function () {
        return app()->getLocale();
    })->name('test');
});
