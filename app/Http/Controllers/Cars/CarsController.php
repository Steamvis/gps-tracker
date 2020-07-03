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
    public function index()
    {
        $cars = Car::with('brand')->whereCompanyId(auth()->user()->company_id)->paginate(10);

        return view('dashboard.cars.index', compact('cars'));
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
