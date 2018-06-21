# Online Status

This packages adds functionality to show user online status, online users and online user number in your laravel app. This package can be used in __laravel 5.5 or higher__.

## Installation

This packages uses __redis__ behind the screen. So please install redis in your machine in order to use package.

```bash
composer require zaichaopan/online-status
```

## Usage

* Add __HasOnlineStatus__ trait to your user model

```php
//...
use Zaichaopan\OnlineStatus\HasOnlineStatus;

class User extends Model
{
    use HasOnlineStatus;

}
```

* Set online expiration time.

This package uses __lifetime__ in __config/session.php__ as the default user online expiration time. If user remains inactive during this time period, he or she will be considered offline. So if you want to customize. you can override it in __config/session.php__ or you can override it in User model as follow

```php
// ...
class User extends Model
{
    use HasOnlineStatus;

    public static function getOnlineExpirationInMinutes(): int
    {
        return 10;
    }
}
```

* Apply __UserOnline__ Middleware.php

```php
// App\Http\Kernel.php
class Kernel extends HttpKernel
{
    // ...
    protected $middlewareGroups = [
        'web' => [
           \\...
           \Zaichaopan\OnlineStatus\Middleware\UserOnline::class
        ],
        //...
    ];
}
```

Now when authenticated user makes a web request, his or her online status will be automatically be set.

```php
//
class UserOnline
{
   // ...
    public function handle(Request $request, Closure $next)
    {
        optional($request->user())->online();

        return $next($request);
    }
}
```

## Available Apis

* Get whether user is online:

```php
$status = $user->isOnline;

// or
$status = $user->isOnline();
```

* Get total online user number

```php
$onlineUserCount = User::onlineCount();
```

* Get online users

```php
$onlineUsers = User::ofOnline()->get();

// or
$onlineUsers = User::ofOnline()->paginate();
```

* Set user online

As long as you apply the __UserOnline__ middleware properly, it will automatically set and update authenticated user online status. In case you want to set it manually, you can use __online__ method provided by the trait.

```php
$user->online();
```

* Set user offline

This package listens for __Logout__ event. When user logs out, it will set user online status as offline. In case you may want to set user offline manually, you can use __offline__ method provided by the trait.

```php
$use->offline();
```

## Events

This packages raises two events during the updating user online status process. You may attach listeners to these two events in your EventServiceProvider:

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'Zaichaopan\OnlineStatus\Events\Online' => [
        'App\Listeners\Online',
    ],

    'Zaichaopan\OnlineStatus\Events\Offline' => [
        'App\Listeners\Offline',
    ]
];
```

The __Online__ event will only fire once. It won't fire again if the user is already online
