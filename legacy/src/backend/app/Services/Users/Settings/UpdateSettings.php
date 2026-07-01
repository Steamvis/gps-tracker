<?php

namespace App\Services\Users\Settings;

use App\Models\Setting;
use App\Models\User\UserSettings;
use App\Services\AbstractBaseService;

class UpdateSettings extends AbstractBaseService
{
    public function rules(): array
    {
        return [
            Setting::MAP_CAR_POINT_FROM_STORAGE => 'required|boolean',
            Setting::CAR_DATATABLE_PAGINATE     => 'required|integer|in:' . implode(
                    ',',
                    Setting::find('2')->value_variants
                ),
        ];
    }

    public function execute(array $data)
    {
        $data[Setting::MAP_CAR_POINT_FROM_STORAGE] = $data[Setting::MAP_CAR_POINT_FROM_STORAGE] === null
            ? false : true;

        $this->validate($data);

        $settings = UserSettings::with('relationSetting')->whereUserId(auth()->id())->get();

        foreach ($settings as $setting) {
            $value = $data[$setting->relationSetting->name];

            $setting->update(['value' => $value]);
        }
    }
}
