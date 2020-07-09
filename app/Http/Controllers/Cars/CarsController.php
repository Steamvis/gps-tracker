<?php

namespace App\Http\Controllers\Cars;

use App\Http\Controllers\Controller;
use App\Models\Car\Car;
use App\Models\Country;
use App\Services\Cars\CreateCar;
use App\Services\Cars\DestroyCar;
use App\Services\Cars\DestroyCars;
use App\Services\Cars\UpdateCar;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class CarsController extends Controller
{
    public function index(Request $request)
    {
        $cars = Car::with('brand', 'points')->whereCompanyId(auth()->user()->company_id);

        $cars = $cars->paginate(10);

        //////////////////////////////////
        $carsConnectedCounter = $cars->filter(function ($car) {
            return $car->isConnectedMap;
        })->count();

        $carsDisconnectedCounter = $cars->filter(function ($car) {
            return !$car->isConnectedMap;
        })->count();
        ///////////////////////////////

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
            'mark_id'     => !$request->mark_id ? 1 : $request->mark_id,
            'image'       => $request->image
        ]);

        Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.create'));
        return redirect(route('cars.index', app()->getLocale()));
    }

    public function show(string $locale, Car $car)
    {
        return $car;
    }

    public function destroy(string $locale, Car $car)
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

    public function edit(string $locale, Car $car)
    {
        return $this->create()->with('car', $car);
    }

    public function update(Request $request, string $locale, Car $car)
    {
        app(UpdateCar::class)->execute([
            'id'          => $car->id,
            'name'        => $request->name,
            'color'       => $request->color,
            'company_id'  => auth()->user()->company->id,
            'vin_number'  => $request->vin_number,
            'gov_number'  => $request->gov_number,
            'description' => $request->description,
            'year'        => $request->year,
            'mark_id'     => !$request->mark_id ? 1 : $request->mark_id,
            'image'       => $request->image
        ]);

        return back();
    }
}
