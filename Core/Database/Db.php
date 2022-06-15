<?php namespace Core\Database;

use Exception;

use Interfaces\Database\Connection;

use Config\DatabaseConfig;

use Services\DependencyInjectionService;

class Db
{
    private static Connection $dbConnection;

    public static function getConnection(bool $autoConnection = true): Connection
    {
        if ( self::isConnectionExists() === false ) {
            self::createConnection(DatabaseConfig::get('driver'));
        }

        if ( $autoConnection && self::$dbConnection->isConnected() === false ) {
            $connectionUser = DatabaseConfig::get('username');
            $connectionPassword = DatabaseConfig::get('password');

            self::$dbConnection->connect($connectionUser, $connectionPassword);
        }

        return self::$dbConnection;
    }

    private static function isConnectionExists(): bool
    {
        return isset(self::$dbConnection);
    }

    public static function openConnection(): bool
    {
        $connectionDriver = DatabaseConfig::get('driver');
        $connectionUser = DatabaseConfig::get('username');
        $connectionPassword = DatabaseConfig::get('password');

        self::createConnection($connectionDriver);

        if ( self::$dbConnection->connect($connectionUser, $connectionPassword) === false ) {
            return false;
        }

        return true;
    }

    public static function createConnection(string $driver): void
    {
        $diContainer = DependencyInjectionService::getContainer('wrapper.db-connection');

        self::$dbConnection = $diContainer->get($driver)->getWrappedObject();
    }

    public static function closeConnection(): bool
    {
        if ( self::isConnectionExists() ) {
            return self::$dbConnection->disconnect();
        }

        return false;
    }
}
