<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Country;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index()
    {
        $countries = Country::all();

        return view('dashboard.company.register', compact('countries'));
    }

    public function register(Request $request)
    {
        Company::create([
            'owner_id'   => auth()->user()->id,
            'country_id' => $request->country_id,
            'title'      => $request->title
        ]);

        return redirect(route('dashboard.index', app()->getLocale()));
    }
}
