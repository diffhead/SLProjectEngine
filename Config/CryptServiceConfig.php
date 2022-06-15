<?php namespace Config;

use Core\RuntimeConfig;

class CryptServiceConfig extends RuntimeConfig
{
    protected static string $passphrase;
    protected static string $algorythm;
    protected static string $initvector;
}
