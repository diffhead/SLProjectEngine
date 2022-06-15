<?php namespace Services;

class ArrayService
{
    public static function isArray(mixed $isArray): bool
    {
        return is_array($isArray);
    }

    public static function isAssoc(array $isAssoc): bool
    {
        return array_keys($isAssoc) !== range(0, sizeof($isAssoc) - 1);
    }

    public static function isEmpty(array $isEmpty): bool
    {
        return empty($isEmpty);
    }

    public static function sizeOf(array $array): int
    {
        return sizeof($array);
    }

    public static function inArray(array $array, mixed $value): bool
    {
        return in_array($value, $array);
    }

    public static function getKeys(array $array): array
    {
        return array_keys($array);
    }

    public static function getValues(array $array): array
    {
        return array_values($array);
    }

    public static function merge(): array
    {
        $arguments = func_get_args();
        $arrays = [];

        foreach ( $arguments as $argument ) {
            if ( self::isArray($argument) ) {
                $arrays[] = $argument;
            }
        }

        return call_user_func_array('array_merge', $arrays);
    }

    public static function slice(array $array, int $offset, int|null $length = null, bool $preserveKeys = false): array
    {
        return array_slice($array, $offset, $length, $preserveKeys);
    }

    public static function pop(array $array): mixed
    {
        return array_pop($array);
    }

    public static function shift(array $array): mixed
    {
        return array_shift($array);
    }

    public static function intersect(): mixed
    {
        $arguments = func_get_args();
        $arrays = [];

        foreach ( $arguments as $argument ) {
            if ( self::isArray($argument) ) {
                $arrays[] = $argument;
            }
        }

        return call_user_func_array('array_intersect', $arrays);
    }

    public static function sort(array $array, int $flags = SORT_REGULAR): array
    {
        sort($array, $flags);

        return $array;
    }

    public static function sortMultiArrayByKey(array $arrays, string $key, int $flag = SORT_ASC): array
    {
        $columnValues = array_column($arrays, $key);

        array_multisort($columnValues, $flag, $arrays, $flag);

        return $arrays;
    }
}
