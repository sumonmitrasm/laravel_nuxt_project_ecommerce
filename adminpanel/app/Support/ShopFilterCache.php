<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class ShopFilterCache
{
    private const VERSION_KEY = 'api.shop-filter.version';

    public static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
    }

    public static function invalidate(): void
    {
        self::version();
        Cache::increment(self::VERSION_KEY);
    }
}
