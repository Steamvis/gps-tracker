<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\LocaleResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendResetPasswordEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    protected User   $user;
    protected string $token;

    public function __construct(User $user, $token)
    {
        $this->user  = $user;
        $this->token = $token;
    }

    public function handle()
    {
        info('user: ' . $this->user->id . ' send reset password mail');
        $this->user->notify(new LocaleResetPassword($this->token));
    }
}
