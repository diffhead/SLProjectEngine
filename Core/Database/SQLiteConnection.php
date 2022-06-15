<?php namespace Core\Database;

use Interfaces\Database\Query;
use Interfaces\Database\Connection;

use SQLite3 as SQLite;
use SQLite3Result as SQLiteResult;

use Services\HelperService;

class SQLiteConnection implements Connection
{
    private string             $database;
    private string             $host;
    private int                $port;
    private SQLite             $connection;
    private false|SQLiteResult $result;

    public function __construct(string $dbname, string $host, int $port)
    {
        $this->database = $dbname;
        $this->host = $host;
        $this->port = $port;
    }

    public function isConnected(): bool
    {
        return isset($this->connection) && $this->connection instanceof SQLite;
    }

    public function connect(string $username, string $password): bool
    {
        if ( $this->isConnected() ) {
            return false;
        }

        $this->connection = new SQLite(_APP_BASE_DIR_ . 'SQLite/' . $this->database);

        return $this->isConnected();
    }

    public function disconnect(): bool
    {
        if ( $this->isConnected() ) {
            return $this->connection->close();
        }

        return false;
    }

    public function execute(Query $query): bool
    {
        $this->result = false;

        $queryString = $query->getString();

        if ( $this->isConnected() ) {
            $this->result = $this->connection->query($queryString);

            if ( HelperService::isBool($this->result) ) {
                return $this->result;
            }

            return true;
        }

        return false;
    }

    public function fetch(): array
    {
        $array = [];

        if ( HelperService::isBool($this->result) ) {
            return $array;
        }

        while ( $item = $this->result->fetchArray(SQLITE3_ASSOC) ) {
            $array[] = $item;
        }

        return $array;
    }

    public function escapeString(string $string): string
    {
        return SQLite::escapeString($string);
    }

    public function getError(): int
    {
        if ( $this->isConnected() ) {
            return $this->connection->lastErrorCode();
        }

        return Connection::ERROR_NONE;
    }
}
