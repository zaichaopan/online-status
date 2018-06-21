<?php

namespace Zaichaopan\OnlineStatus;

use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Builder;
use Zaichaopan\OnlineStatus\Events\UserOnline;
use Zaichaopan\OnlineStatus\Events\UserOffline;

trait HasOnlineStatus
{
    public function isOnline(): bool
    {
        $time = Redis::zscore(static::getOnlineCacheKey(), $this->getSortedSetMember());

        if (is_null($time)) {
            return false;
        }

        return (int) $time > $this->freshTimestamp()->subMinutes(static::getOnlineExpirationInMinutes())->timestamp;
    }

    public function online($time = null): void
    {
        $time = $time ?? time();

        if (!$this->isOnline()) {
            event(new UserOnline($this));
        }

        Redis::zadd(static::getOnlineCacheKey(), $time, $this->getSortedSetMember());
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }

    public function offline(): void
    {
        Redis::zrem(static::getOnlineCacheKey(), $this->getSortedSetMember());
        event(new UserOffline($this));
    }

    public function scopeOfOnline(Builder $builder): Builder
    {
        return $builder->whereIn('id', static::getOnlineUserIds());
    }

    public static function getOnlineUserIds(): ?array
    {
        return Redis::zrevrangebyscore(static::getOnlineCacheKey(), '+inf', static::getMinScore());
    }

    public static function onlineCount(): int
    {
        return Redis::zcount(static::getOnlineCacheKey(), static::getMinScore(), '+inf');
    }

    public static function getOnlineExpirationInMinutes(): int
    {
        return config('session.lifetime');
    }

    protected static function getMinScore(): int
    {
        return now()->subSeconds(static::getOnlineExpirationInMinutes() * 60)->timestamp;
    }

    protected static function getOnlineCacheKey(): string
    {
        return 'users.online';
    }

    protected function getSortedSetMember(): string
    {
        return $this->id;
    }
}
