<?php namespace Config;

use Core\RuntimeConfig;

class DatabaseConfig extends RuntimeConfig
{
    protected static string $driver;
    protected static string $host;
    protected static int    $port;
    protected static string $username;
    protected static string $password;
    protected static string $database;
}
