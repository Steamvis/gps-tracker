<?php

namespace App\Models;

use App\Helpers\AvatarGenerator;
use App\Jobs\SendResetPasswordEmailJob;
use App\Jobs\SendVerifyEmailJob;
use App\Models\User\UserSettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function getAvatarAttribute()
    {
        return app(AvatarGenerator::class)->generate($this->first_name, $this->last_name);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSettings::class, 'user_id', 'id');
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_id', 'id');
    }

    public function sendEmailVerificationNotification()
    {
        SendVerifyEmailJob::dispatch($this)->delay(now()->addSeconds(3));
    }

    public function sendPasswordResetNotification($token)
    {
        SendResetPasswordEmailJob::dispatch($this, $token)->delay(now()->addSeconds(3));
    }
}
