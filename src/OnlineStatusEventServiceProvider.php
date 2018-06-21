<?php

namespace Zaichaopan\OnlineStatus;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class OnlineStatusEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Logout' => [
            'Zaichaopan\OnlineStatus\Listeners\LogoutListener',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
