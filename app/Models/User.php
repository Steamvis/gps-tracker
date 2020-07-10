<?php

namespace App\Models;

use App\Jobs\SendResetPasswordEmailJob;
use App\Jobs\SendVerifyEmailJob;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    const
        LEVEL_ADMIN = 100,
        LEVEL_COMPANY_OWNER = 50,
        LEVEL_UNVERIFIED = 0;

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
        'level',
        'company_id',
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

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_id', 'id');
    }

    /**
     * Changes laravel mail template to localized
     */
    public function sendEmailVerificationNotification()
    {
        SendVerifyEmailJob::dispatch($this)->delay(now()->addSeconds(3));
    }

    /**
     * Changes laravel mail template to localized
     *
     * @param $token
     */
    public function sendPasswordResetNotification($token)
    {
        SendResetPasswordEmailJob::dispatch($this, $token)->delay(now()->addSeconds(3));
    }
}
