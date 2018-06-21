<?php

namespace Zaichaopan\OnlineStatus\Listeners;

class LogoutListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  auth.logout  $event
     * @return void
     */
    public function handle($event)
    {
        $event->user->offline();
    }
}
