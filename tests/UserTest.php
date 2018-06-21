<?php

use Illuminate\Support\Facades\Event;
use Zaichaopan\OnlineStatus\Events\UserOnline;
use Zaichaopan\OnlineStatus\Events\UserOffline;

class UserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['email' => 'john@example.com']);
    }

    /** @test */
    public function it_can_set_user_online_status()
    {
        $this->assertFalse($this->user->isOnline());
        $this->assertFalse($this->user->isOnline);

        $this->user->online();

        $this->assertTrue($this->user->isOnline());
        $this->assertTrue($this->user->isOnline);

        $time = now()->subSeconds(User::getOnlineExpirationInMinutes() * 60 + 1)->timestamp;
        $this->user->online($time);

        $this->assertFalse($this->user->isOnline());
        $this->assertFalse($this->user->isOnline);
    }

    /** @test */
    public function it_can_set_online_user_offline()
    {
        $this->assertFalse($this->user->isOnline());
        $this->assertFalse($this->user->isOnline);

        $this->user->online();

        $this->assertTrue($this->user->isOnline());
        $this->assertTrue($this->user->isOnline);

        $this->user->offline();

        $this->assertFalse($this->user->isOnline());
        $this->assertFalse($this->user->isOnline);
    }

    /** @test */
    public function it_can_online_user_count()
    {
        $this->assertEquals(0, User::onlineCount());

        $this->user->online();

        $this->assertEquals(1, User::onlineCount());

        $jane = User::create(['email' => 'jane@example.com']);

        $jane->online();

        $this->assertEquals(2, User::onlineCount());

        $time = now()->subSeconds(User::getOnlineExpirationInMinutes() * 60 + 1)->timestamp;
        $this->user->online($time);

        $this->assertEquals(1, User::onlineCount());

        $jane->offline();

        $this->assertEquals(0, User::onlineCount());
    }

    /** @test */
    public function it_can_get_online_user_ids()
    {
        $this->assertCount(0, User::getOnlineUserIds());
        $this->user->online();

        $this->assertCount(1, $onlineUserIds = User::getOnlineUserIds());
        $this->assertContains($this->user->id, $onlineUserIds);

        $jane = User::create(['email' => 'jane@example.com']);
        $jane->online();

        $this->assertCount(2, $onlineUserIds = User::getOnlineUserIds());
        $this->assertContains($this->user->id, $onlineUserIds);
        $this->assertContains($jane->id, $onlineUserIds);

        $time = now()->subSeconds(User::getOnlineExpirationInMinutes() * 60 + 1)->timestamp;
        $this->user->online($time);

        $this->assertCount(1, $onlineUserIds = User::getOnlineUserIds());
        $this->assertContains($jane->id, $onlineUserIds);

        $jane->offline();

        $this->assertCount(0, User::getOnlineUserIds());
    }

    /** @test */
    public function it_can_get_online_users()
    {
        $users = User::ofOnline()->get();
        $this->assertCount(0, $users);

        $this->user->online();
        $users = User::ofOnline()->get();
        $this->assertCount(1, $users);
        $this->assertEquals($this->user->id, $users->first()->id);

        $jane = User::create(['email' => 'jane@example.com']);
        $jane->online();

        $this->assertCount(2, $onlineUserIds = User::ofOnline()->get()->pluck('id')->toArray());
        $this->assertContains($this->user->id, $onlineUserIds);
        $this->assertContains($jane->id, $onlineUserIds);

        $time = now()->subSeconds(User::getOnlineExpirationInMinutes() * 60 + 1)->timestamp;
        $this->user->online($time);

        $this->assertCount(1, $onlineUserIds = User::ofOnline()->get()->pluck('id')->toArray());
        $this->assertContains($jane->id, $onlineUserIds);

        $jane->offline();

        $this->assertCount(0, User::ofOnline()->get());
    }

    /** @test */
    public function it_will_fire_user_online_event_when_user_online()
    {
        Event::fake();

        $this->user->online();

        Event::assertDispatched(UserOnline::class, function ($e) {
            return $e->user->id === $this->user->id;
        });
    }

    /** @test */
    public function it_will_not_fire_user_online_event_when_user_is_already_online()
    {
        Event::fake();

        $this->user->online();

        $this->user->online();

        Event::assertDispatched(UserOnline::class, 1);
    }

    /** @test */
    public function it_will_fire_user_offline_event_when_user_offline()
    {
        Event::fake();

        $this->user->online();

        $this->user->offline();

        Event::assertDispatched(UserOffline::class, function ($e) {
            return $e->user->id === $this->user->id;
        });
    }
}
