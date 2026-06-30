<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class SendWelcomeEmail
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user->etablissement) {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        }
    }
}
