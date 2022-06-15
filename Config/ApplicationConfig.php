<?php namespace Config;

use Core\RuntimeConfig;

class ApplicationConfig extends RuntimeConfig
{
    protected static bool   $dev;
    protected static int    $port;
    protected static string $host;
    protected static bool   $modules;
    protected static string $cacheDir;
}
