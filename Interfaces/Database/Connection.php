<?php namespace Interfaces\Database;

interface Connection
{
    const ERROR_NONE   = 0;
    const ERROR_EXISTS = 1;

    public function __construct(string $dbname, string $host, int $port);
    public function isConnected(): bool;
    public function connect(string $username, string $password): bool;
    public function disconnect(): bool;
    public function execute(Query $query): bool;
    public function fetch(): array;
    public function escapeString(string $string): string;
    public function getError(): int;
}
