<?php

namespace App\Http\Controllers\Cars;

use App\Helpers\CarHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyCarsRequest;
use App\Http\Requests\StoreCarRequest;
use App\Models\Car\Car;
use App\Models\Country;
use App\Services\Cars\CreateCar;
use App\Services\Cars\DestroyCar;
use App\Services\Cars\DestroyCars;
use App\Services\Cars\UpdateCar;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CarsController extends Controller
{
    public function index(Request $request)
    {
        $paginatePages = auth()->user()->settings->where('setting_id', 2)->first()->value;
        $cars = Car::with('brand', 'points')->whereCompanyId(auth()->user()->company_id);

        if ($request->has('search')) {
            $search = $request->search;
            $cars = $cars->where(function ($query) use ($search) {
                $query
                    ->where('name', 'LIKE', "%{$search}%",)
                    ->orWhere('vin_number', 'LIKE', "%{$search}%")
                    ->orWhere('gov_number', 'LIKE', "%{$search}%")
                    ->orWhere('api_code', 'LIKE', "%{$search}%")
                    ->orWhere('year', $search)
                    ->orWhereIn('mark_id',
                        fn($query) => $query->select('id')->from('car_marks')->where('name', 'LIKE', "%{$search}%")
                    );
            });
        }

        $cars = $cars->paginate($paginatePages);

        return view('dashboard.cars.index', [
            'cars'   => $cars,
            'search' => $search ?? ''
        ]);
    }

    public function create()
    {
        $countries = Country::with(['brands'])->get();

        return view('dashboard.cars.create', compact('countries'));
    }

    public function store(StoreCarRequest $request)
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
            'image'       => $request->image,
        ]);

        Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.create'));

        return redirect(route('cars.index', app()->getLocale()));
    }

    public function show(string $locale, Car $car)
    {
        if (CarHelper::checkAuthUserOwnsCar($car)) {
            return view('dashboard.cars.show', compact('car'));
        }
        return abort(404);
    }

    public function destroy(string $locale, Car $car)
    {
        $result = app(DestroyCar::class)->execute([
            'id' => $car->id
        ]);

        $result
            ? Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.delete'))
            : Alert::error(__('dashboard.general.result.error'), __('dashboard.cars.result.delete'));

        return redirect()->back();
    }

    public function destroyMany(DestroyCarsRequest $request)
    {
        $result = app(DestroyCars::class)->execute([
            'action' => $request->action,
        ]);

        $result
            ? Alert::success(__('dashboard.general.result.success'), __('dashboard.cars.result.delete'))
            : Alert::error(__('dashboard.general.result.error'), __('dashboard.cars.result.delete'));


        return redirect()->back();
    }

    public function edit(string $locale, Car $car)
    {
        if (CarHelper::checkAuthUserOwnsCar($car)) {
            return $this->create()->with('car', $car);
        }
        return abort(404);
    }

    public function update(StoreCarRequest $request, string $locale, Car $car)
    {
        $result = app(UpdateCar::class)->execute([
            'id'          => $car->id,
            'name'        => $request->name,
            'color'       => $request->color,
            'company_id'  => auth()->user()->company->id,
            'vin_number'  => $request->vin_number,
            'gov_number'  => $request->gov_number,
            'description' => $request->description,
            'year'        => $request->year,
            'mark_id'     => !$request->mark_id ? 1 : $request->mark_id,
            'image'       => $request->image,
        ]);

        $result
            ? Alert::success(__('dashboard.general.result.success'), __('dashboard.general.result.update'))
            : Alert::error(__('dashboard.general.result.error'), __('dashboard.general.result.update'));

        return back();
    }
}
