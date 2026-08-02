<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class DashboardCache
{
    private const VERSION_KEY = 'dashboard:data-version';

    public static function version(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if (! is_numeric($version)) {
            Cache::forever(self::VERSION_KEY, 1);

            return 1;
        }

        return (int) $version;
    }

    public static function bump(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    public static function forgetAll(): void
    {
        self::bump();
    }
}
