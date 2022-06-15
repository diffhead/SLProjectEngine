<?php namespace Interfaces;

interface LoggerStatic
{
    public static function getInstance(): Logger;

    public static function log(string $message, int $level = E_NOTICE): bool;

    public static function notice(string $message): bool;
    public static function warning(string $message): bool;
    public static function error(string $message): bool;
}
