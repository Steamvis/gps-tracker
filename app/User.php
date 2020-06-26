<?php

namespace App;

use App\Notifications\LocaleResetPassword;
use App\Notifications\LocaleVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'locale',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Changes laravel mail template to localized
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new LocaleVerifyEmail());
    }

    /**
     * Changes laravel mail template to localized
     *
     * @param $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new LocaleResetPassword($token));
    }
}
