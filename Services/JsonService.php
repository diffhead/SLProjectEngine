<?php namespace Services;

class JsonService
{
    public static function decode(string $jsonString, null|bool $associative = true, int $depth = 512, int $flags = 0): array
    {
        return (array)json_decode($jsonString, $associative, $depth, $flags);
    }

    public static function encode(mixed $jsonData, int $flags = 0, int $depth = 512): string
    {
        return (string)json_encode($jsonData, $flags, $depth);
    }

    public static function lastError(): int
    {
        return json_last_error();
    }
}
