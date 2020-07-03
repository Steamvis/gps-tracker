<?php

namespace App\Http\Controllers;

use App\CarRoute;

class DashboardController extends Controller
{
    public function index()
    {
        $routes = CarRoute::all();

        return view('dashboard.index', compact('routes'));
    }
}
