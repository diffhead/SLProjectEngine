<?php namespace Lib;

use Memcached as MemcachedEnv;

use Config\MemcachedConfig;

class Memcached
{
    private static MemcachedEnv $memcached;
    private static bool         $enabled;

    public function __construct()
    {
        if ( isset(self::$memcached) === false ) {
            self::initMemcached();
        }
    }

    private static function initMemcached(): void
    {
        if ( MemcachedConfig::get('enabled') ) {
            $memcached = new MemcachedEnv();

            foreach ( MemcachedConfig::get('servers') as $server ) {
                $memcached->addServer($server['host'], $server['port']);
            }

            self::$memcached = $memcached;
            self::$enabled = true;
        } else {
            self::$enabled = false;
        }
    }

    public function get(string $key): mixed
    {
        if ( self::isEnabled() === false ) {
            return false;
        }

        return self::$memcached->get($key);
    }

    private static function isEnabled(): bool
    {
        return self::$enabled;
    }

    public function set(string $key, mixed $value, int $expires = 3600 * 24): bool
    {
        if ( self::isEnabled() === false ) {
            return false;
        }

        return self::$memcached->set($key, $value, $expires);
    }

    public function flush(): bool
    {
        if ( self::isEnabled() === false ) {
            return false;
        }

        return self::$memcached->flush();
    }

    public function getCode(): int
    {
        if ( self::isEnabled() ) {
            return MEMCACHED_CONNECTION_FAILURE;
        }

        return self::$memcached->getResultCode();
    }
}
