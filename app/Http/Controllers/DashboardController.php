<?php

namespace App\Http\Controllers;

use App\Models\Car\Car;
use App\Models\Car\CarPoint;
use App\Models\Car\CarRoute;
use App\Models\Car\CarRouteSection;

class DashboardController extends Controller
{
    public function index()
    {
        $cars = Car::whereHas('points')->where('company_id', auth()->user()->company_id)->get();




//        $routes = CarRoute::with(['sections.start_point', 'sections.end_point'])->get();

//            CarPoint::whereIn(''id', $route->sections->pluck('start_point_id')
//              ->merge($route->sections->pluck('end_point_id'))->unique())
        return view('dashboard.index', compact('cars'));
    }
}
