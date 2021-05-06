<?php

namespace Zaichaopan\OnlineStatus;

use Illuminate\Support\Carbon;
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
        $this->updateLastChangedOnlineStatus();
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }

    public function getLastChangedOnlineStatusAttribute(): ?Carbon
    {
        $timestamp = $this->getLastChangedOnlineTimestamp();
        if (! $timestamp) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp);
    }

    public function offline(): void
    {
        Redis::zrem(static::getOnlineCacheKey(), $this->getSortedSetMember());
        $this->updateLastChangedOnlineStatus();
        event(new UserOffline($this));
    }

    public function scopeOfOnline(Builder $builder): Builder
    {
        return $builder->whereIn('id', static::getOnlineUserIds());
    }

    public static function getOnlineUserIds(): array
    {
        $result = Redis::zrevrangebyscore(
            static::getOnlineCacheKey(),
            '+inf',
            static::getMinScore()
        );
        return is_array($result) ? array_map(static fn($v) => (int)$v, $result) : [];
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

    protected function updateLastChangedOnlineStatus(): void
    {
        $key = static::getLastChangedOnlineStatusCacheKey();
        Redis::zadd($key, time(), $this->getSortedSetMember());
    }

    protected function getLastChangedOnlineTimestamp(): int
    {
        $key = static::getLastChangedOnlineStatusCacheKey();
        return (int)Redis::zscore($key, $this->getSortedSetMember());
    }

    protected static function getLastChangedOnlineStatusCacheKey(): string
    {
        return 'users.last-changed-online';
    }

    protected function getSortedSetMember(): string
    {
        return $this->id;
    }
}
