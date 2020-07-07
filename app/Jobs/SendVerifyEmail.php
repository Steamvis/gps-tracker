<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\LocaleVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendVerifyEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        info('user: ' . $this->user->id . ' send verify mail');
        $this->user->notify(new LocaleVerifyEmail());
    }
}
