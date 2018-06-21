<?php

class LogoutListenerTest extends TestCase
{
    /** @test */
    public function it_will_set_user_offline_when_user_logout()
    {
        auth()->setUser($user = User::create(['email' => 'john@example.com']));
        $user->online();

        $this->assertTrue($user->isOnline);

        auth()->logout();

        $this->assertFalse($user->isOnline);
    }
}
