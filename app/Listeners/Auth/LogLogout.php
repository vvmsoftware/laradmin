<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserLoggedIn $event): void
    {
        // Log the login event
        activity()
            ->by($event->user)
            ->setEvent('logged_out')
            ->log('User logged out');
    }
}
