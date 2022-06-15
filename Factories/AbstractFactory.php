<?php namespace Factories;

use ReflectionClass;

use Services\ArrayService;
use Services\StringService;

abstract class AbstractFactory
{
    protected static string $class;

    public static function get(): Object
    {
        if ( StringService::isEmpty(static::$class) ) {
            throw new Exception(StringService::concat("Empty class definition of the", __CLASS__));
        }

        $methodArgs = func_get_args();

        if ( ArrayService::isEmpty($methodArgs) ) {
            return new static::$class();
        }

        $reflectionClass = new ReflectionClass(static::$class);

        return $reflectionClass->newInstanceArgs($methodArgs);
    }
}
