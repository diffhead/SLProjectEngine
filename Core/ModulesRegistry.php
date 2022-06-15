<?php namespace Core;

use Core\AbstractModule;

class ModulesRegistry
{
    private static array $_INSTANCES = [];

    public static function setModule(string $name, AbstractModule $instance): void
    {
        self::$_INSTANCES[$name] = $instance;
    }

    public static function getModule(string $name): null|AbstractModule
    {
        if ( isset(self::$_INSTANCES[$name]) === false ) {
            return null;
        }

        return self::$_INSTANCES[$name];
    }
}
