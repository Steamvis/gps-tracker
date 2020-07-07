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
        $cars = Car::with('routes.sections.points', 'brand', 'routes.points')
            ->whereHas('points')
            ->where('company_id', auth()->user()->company_id)
            ->get();

        return view('dashboard.index', compact('cars'));
    }
}
