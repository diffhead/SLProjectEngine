<?php namespace Services;

class HelperService
{
    public static function isNull(mixed $isNull): bool
    {
        return is_null($isNull);
    }

    public static function isBool(mixed $isBool): bool
    {
        return is_bool($isBool);
    }

    public static function isNumeric(mixed $isNumeric): bool
    {
        return is_numeric($isNumeric);
    }

    public static function isCallable(array|string $isCallable): bool
    {
        return is_callable($isCallable);
    }

    public static function serialize(mixed $value): string
    {
        return serialize($value);
    }

    public static function unserialize(string $serialized): mixed
    {
        return unserialize($serialized);
    }
}
