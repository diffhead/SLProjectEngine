<?php namespace Core\Database;

use Interfaces\Database\Query;
use Interfaces\Database\Connection;

use PgSql\Connection as PgSQL;
use PgSql\Result     as PgSQlResult;

class PgSQLConnection implements Connection
{
    private string            $database;
    private string            $host;
    private int               $port;
    private false|PgSQL       $connection;
    private false|PgSQLResult $result;

    public function __construct(string $dbname, string $host, int $port)
    {
        $this->database = $dbname;
        $this->host = $host;
        $this->port = $port;
    }

    public function isConnected(): bool
    {
        return isset($this->connection) && $this->connection instanceof PgSQL;
    }

    public function connect(string $username, string $password): bool
    {
        if ( $this->isConnected() ) {
            return false;
        }

        $this->connection = pg_connect($this->getConnectString($username, $password));

        return (bool)$this->connection;
    }

    private function getConnectString(string $username, string $password): string
    {
        return "host={$this->host} port={$this->port} dbname={$this->database} user={$username} password={$password}";
    }

    public function disconnect(): bool
    {
        if ( $this->isConnected() ) {
            pg_close($this->connection);

            return true;
        }

        return false;
    }

    public function execute(Query $query): bool
    {
        $queryString = $query->getString();

        $this->result = pg_query($this->connection, $queryString);

        if ( $this->result === false ) {
            return false;
        }

        return true;
    }

    public function fetch(): array
    {
        if ( $this->result === false ) {
            return [];
        }

        return pg_fetch_all($this->result);
    }

    public function escapeString(string $string): string
    {
        return pg_escape_string($string);
    }

    public function getError(): int
    {
        if ( $this->isConnected() && pg_last_error($this->connection) !== _APP_EMPTY_STRING_ ) {
            return Connection::ERROR_EXISTS;
        }

        return Connection::ERROR_NONE;
    }
}
