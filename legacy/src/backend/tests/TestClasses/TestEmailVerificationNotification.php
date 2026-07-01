<?php

namespace Tests\TestClasses;

use App\Notifications\LocaleVerifyEmail;

class TestEmailVerificationNotification extends LocaleVerifyEmail
{
    public function verificationUrl($notifiable)
    {
        return parent::verificationUrl($notifiable);
    }
}
