<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class ContentCache
{
    /**
     * @template T
     * @param Closure(): T $load
     * @return T
     */
    public static function remember(string $group, string $name, Closure $load): mixed
    {
        if (DB::transactionLevel() > 0) {
            return $load();
        }

        // The database is the authority, even if Redis was offline during an edit.
        $version = DB::table('content_cache_versions')->where('group', $group)->value('version') ?? 'initial';
        $key = "content.v1.{$group}.{$name}";
        $failed = false;
        $cached = self::cacheOperation(fn () => Cache::get($key), $failed);
        if ($failed) {
            return $load();
        }
        if (self::matches($cached, $version)) {
            return $cached['value'];
        }

        $lock = self::cacheOperation(function () use ($group) {
            $lock = Cache::lock("content.v1.{$group}.lock", 60);
            $lock->block(2);
            return $lock;
        });
        if (!$lock) {
            return $load();
        }

        try {
            $cached = self::cacheOperation(fn () => Cache::get($key));
            if (self::matches($cached, $version)) {
                return $cached['value'];
            }

            // Database/application exceptions must not be mistaken for cache failures.
            $value = $load();
            self::cacheOperation(function () use ($group, $key, $version, $value) {
                $index = "content.v1.{$group}.keys";
                $keys = Cache::get($index, []);
                $keys[$key] = true;
                Cache::forever($index, $keys);
                Cache::forever($key, ['version' => $version, 'value' => $value]);
            });
            return $value;
        } finally {
            self::cacheOperation(fn () => $lock->release());
        }
    }

    public static function forget(string $group): void
    {
        // Persist first. Failed cleanup must never make old cache entries valid.
        DB::table('content_cache_versions')->upsert(
            [['group' => $group, 'version' => (string) Str::uuid()]],
            ['group'],
            ['version']
        );

        self::cacheOperation(function () use ($group) {
            Cache::lock("content.v1.{$group}.lock", 60)->block(2, function () use ($group) {
                $index = "content.v1.{$group}.keys";
                foreach (array_keys(Cache::get($index, [])) as $key) {
                    Cache::forget($key);
                }
                Cache::forget($index);
            });
        });
    }

    private static function matches(mixed $cached, string $version): bool
    {
        return is_array($cached)
            && ($cached['version'] ?? null) === $version
            && array_key_exists('value', $cached);
    }

    /**
     * @template T
     * @param Closure(): T $operation
     * @return T|null
     */
    private static function cacheOperation(Closure $operation, bool &$failed = false): mixed
    {
        try {
            return $operation();
        } catch (LockTimeoutException $exception) {
            return null;
        } catch (Throwable $exception) {
            $failed = true;
            // Never log connection credentials or cached customer data.
            try {
                Log::warning('Content cache unavailable; using database data.', [
                    'exception_type' => get_class($exception),
                ]);
            } catch (Throwable) {
                // A failed log destination must not break the fallback response.
            }
            return null;
        }
    }
}
