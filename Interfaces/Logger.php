<?php namespace Interfaces;

interface Logger
{
    public function log(string $message, int $level = E_NOTICE): bool;

    public function notice(string $message): bool;
    public function warning(string $message): bool;
    public function error(string $message): bool;
}
