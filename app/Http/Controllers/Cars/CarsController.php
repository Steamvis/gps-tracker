<?php

namespace App\Http\Controllers\Cars;

use App\Http\Controllers\Controller;
use App\Models\Car\Car;
use App\Models\Country;
use App\Services\Cars\CreateCar;
use App\Services\Cars\DestroyCar;
use App\Services\Cars\DestroyCars;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CarsController extends Controller
{
    public function index(Request $request)
    {
        $cars = Car::with('brand', 'points')->whereCompanyId(auth()->user()->company_id);

        if ($request->ajax()) {
            $cars = $cars->select([
                'id',
                'name',
                'mark_id',
                'api_code',
                'gov_number',
                'vin_number',
                'year',
                'color'
            ]);
        }

        $cars = $cars->paginate(10);

        $carsConnectedCounter = $cars->filter(function ($car) {
            return $car->isConnectedMap;
        })->count();

        $carsDisconnectedCounter = $cars->filter(function ($car) {
            return !$car->isConnectedMap;
        })->count();

//        auth()->user()->company->updateConnecterCarsCounter();

        return view('dashboard.cars.index', compact('cars', 'carsConnectedCounter', 'carsDisconnectedCounter'));
    }

    public function create()
    {
        $countries = Country::with(['brands'])->get();
        return view('dashboard.cars.create', compact('countries'));
    }

    public function store(Request $request)
    {
        app(CreateCar::class)->execute([
            'name'        => $request->name,
            'color'       => $request->color,
            'company_id'  => auth()->user()->company->id,
            'vin_number'  => $request->vin_number,
            'gov_number'  => $request->gov_number,
            'description' => $request->description,
            'year'        => $request->year,
            'mark_id'     => !$request->mark_id ? 1 : $request->mark_id
        ]);

        Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.create'));
        return redirect(route('cars.index', app()->getLocale()));
    }

    public function show()
    {
        dd('show');
    }

    public function destroy($locale, Car $car)
    {
        app(DestroyCar::class)->execute([
            'id' => $car->id
        ]);

        Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.delete'));
        return redirect()->back();
    }

    public function destroyMany(Request $request)
    {
        app(DestroyCars::class)->execute([
            'action' => $request->action
        ]);

        Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.delete'));
        return redirect()->back();
    }
}
