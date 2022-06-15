<?php namespace Interfaces;

interface Configuration 
{
    public static function set(string $code, mixed $value): bool;
    public static function get(string $code): mixed;
}
