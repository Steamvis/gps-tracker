<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Country;
use App\Services\Company\CreateCompany;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index()
    {
        if (auth()->user()->company) {
            return redirect(route('dashboard.index', app()->getLocale()));
        }

        $countries = Country::all();

        return view('dashboard.company.register', compact('countries'));
    }

    public function register(Request $request)
    {
        app(CreateCompany::class)->execute([
            'owner_id'   => auth()->user()->id,
            'country_id' => $request->country_id,
            'title'      => $request->title
        ]);

        return redirect(route('dashboard.index', app()->getLocale()));
    }
}
