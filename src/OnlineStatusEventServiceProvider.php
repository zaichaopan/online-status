<?php

namespace Zaichaopan\OnlineStatus;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Logout;
use Zaichaopan\OnlineStatus\Listeners\LogoutListener;

class OnlineStatusEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Logout::class => [
            LogoutListener::class,
        ],
    ];
}
