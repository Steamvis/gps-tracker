<?php

namespace App\Http\Controllers\Users;

use App\Helpers\UserSettingsHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User\UserSettings;
use App\Services\Users\Settings\UpdateSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function setting()
    {
        $settings = UserSettings::with('relationSetting')->whereUserId(auth()->id())->get();

        $checkboxes = $settings->filter(fn($setting) => $setting->relationSetting->type === Setting::TYPE_CHECKBOX);
        $selects    = $settings->filter(fn($setting) => $setting->relationSetting->type === Setting::TYPE_SELECT);

        return view('dashboard.user.settings', compact('checkboxes', 'selects'));
    }

    public function update(Request $request)
    {
        app(UpdateSettings::class)->execute(
            [
                Setting::MAP_CAR_POINT_FROM_STORAGE => $request->get(Setting::MAP_CAR_POINT_FROM_STORAGE),
                Setting::CAR_DATATABLE_PAGINATE     => $request->get(Setting::CAR_DATATABLE_PAGINATE),
            ]
        );

        return back();
    }
}