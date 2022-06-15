<?php namespace Core\Database;

use Interfaces\Database\Query;
use Interfaces\Database\Connection;

use mysqli as MySQL;
use mysqli_result as MySQLResult;

use Services\HelperService;

class MySQLConnection implements Connection
{
    private string           $database;
    private string           $host;
    private int              $port;
    private MySQL            $connection;
    private bool|MySQLResult $result;

    public function __construct(string $dbname, string $host, int $port)
    {
        $this->database = $dbname;
        $this->host = $host;
        $this->port = $port;
    }

    public function isConnected(): bool
    {
        return isset($this->connection) && $this->connection instanceof MySQL && $this->connection->ping();
    }

    public function connect(string $username, string $password): bool
    {
        if ( $this->isConnected() ) {
            return false;
        }

        $this->connection = new MySQL($this->host, $username, $password, $this->database, $this->port);

        return $this->isConnected();
    }

    public function disconnect(): bool
    {
        if ( $this->isConnected() ) {
            $this->connection->close();

            return true;
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
        if ( HelperService::isBool($this->result) ) {
            return [];
        }

        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

    public function escapeString(string $string): string
    {
        if ( $this->isConnected() ) {
            return $this->connection->real_escape_string($string);
        }

        return mysqli_real_escape_string($string);
    }

    public function getError(): int
    {
        if ( $this->isConnected() ) {
            return $this->connection->errno;
        }

        return Connection::ERROR_NONE;
    }
}
