<?php

namespace Zaichaopan\OnlineStatus;

use Illuminate\Support\Facades\Redis;

trait HasOnlineStatus
{
    public function getExpirationTimeInMinutes()
    {
        return 10;
    }

    public function isOnline(): bool
    {
        $time = Redis::zscore($this->getOnlineCacheKey, $this->getSortedSetMember());

        if (is_null($time)) {
            return false;
        }

        return $this->freshTimestamp()->diffInMinutes($time) < $this->getExpirationTimeInMinutes();
    }

    public function setOnlineStatus(): void
    {
        Redis::zadd($this->getOnlineCacheKey(), now(), $this->getSortedSetMember());
    }

    public function onlineCount(): bool
    {
        $minTime = now()->subMinutes($this->getExpirationTimeInMinutes() + 1);

        return  Redis::zcount($this->getOnlineCacheKey(), $minTime, '+inf');
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }

    public function offline(): void
    {
        Redis::zrem($this->getOnlineCacheKey(), $this->sortedSetMember());
    }

    protected function getOnlineCacheKey(): string
    {
        return 'users.online';
    }

    protected function getSortedSetMember(): string
    {
        return 'user.' . $this->id;
    }
}
