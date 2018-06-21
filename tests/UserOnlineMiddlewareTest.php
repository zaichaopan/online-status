<?php

use Illuminate\Http\Request;
use Zaichaopan\OnlineStatus\Middleware\UserOnline;

class UserOnlineMiddlewareTest extends TestCase
{
    /** @test */
    public function it_will_not_set_user_online_if_user_is_not_logined_in()
    {
        $request = Request::create('/', 'GET');
        $userOnline = new UserOnline;
        $userOnline->handle($request, function () {});

        $this->assertCount(0, User::ofOnline()->get());
    }

    /** @test */
    public function it_will_set_user_online_if_user_is_logined_in()
    {
        $request = Request::create('/', 'GET');
        $user = User::create(['email' => 'john@example.com']);
        $request->setUserResolver(function () use ($user) { return $user; });
        $userOnline = new UserOnline;
        $userOnline->handle($request, function () {});

        $this->assertCount(1, User::ofOnline()->get());
        $this->assertTrue($user->isOnline());
    }
}
