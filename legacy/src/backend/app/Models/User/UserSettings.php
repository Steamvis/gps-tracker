<?php

namespace App\Models\User;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    public $timestamps = false;

    public $fillable = [
        'user_id',
        'setting_id',
        'value',
    ];

    protected $table = 'users_settings';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function relationSetting()
    {
        return $this->hasOne(Setting::class, 'id', 'setting_id');
    }
}