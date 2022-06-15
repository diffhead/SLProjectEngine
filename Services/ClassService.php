<?php namespace Services;

class ClassService
{
    public static function propertyExists(string|object $class, string $property): bool
    {
        return property_exists($class, $property);
    }

    public static function methodExists(string|object $class, string $method): bool
    {
        return method_exists($class, $method);
    }

    public static function getPublicProperties(string|object $class): array
    {
        return StringService::isString($class) ? get_class_vars($class) : get_class_vars(get_class($class));
    }

    public static function isClass(Object $class, string $className): bool
    {
        return is_a($class, $className);
    }
}
