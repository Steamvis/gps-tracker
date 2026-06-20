<?php

namespace App\Models;

use App\Models\User\UserSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Setting extends Model
{
    const
        TYPE_CHECKBOX = 'checkbox',
        TYPE_SELECT = 'select',
        TYPE_TEXT = 'text';

    const
        MAP_CAR_POINT_FROM_STORAGE = 'MAP_CAR_POINT_FROM_STORAGE',
        CAR_DATATABLE_PAGINATE = 'CAR_DATATABLE_PAGINATE';

    public $timestamps = false;

    public $fillable = [
        'name',
        'translate_en',
        'translate_ru',
        'type',
        'value_variants',
    ];


    public function getValueVariantsAttribute(): array
    {
        return explode(',', $this->attributes['value_variants']);
    }

    public function getTranslateAttribute(): string
    {
        $translate = 'translate_' . app()->getLocale();

        return $this->attributes[$translate];
    }

    public function userSetting()
    {
        return $this->hasMany(UserSettings::class, 'setting_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'users_settings',
            'setting_id',
            'user_id'
        );
    }
}