<?php

namespace App\Providers;

use App\Models\Car\Car;
use App\Observers\CarObserver;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        Car::observe(CarObserver::class);

        // ngrok support
        // ngrok http -host-header=rewrite site.dev:80
        // normalizes url
//        if ($request->server->has('HTTP_X_ORIGINAL_HOST')) {
//            $request->server->set('HTTP_HOST', $request->server->get('HTTP_X_ORIGINAL_HOST'));
//            $request->headers->set('HOST', $request->server->get('HTTP_X_ORIGINAL_HOST'));
//        }
    }
}
