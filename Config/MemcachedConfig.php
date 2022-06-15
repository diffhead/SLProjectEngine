<?php namespace Config;

use Core\RuntimeConfig;

class MemcachedConfig extends RuntimeConfig
{
    protected static bool  $enabled;
    protected static array $servers;
}
