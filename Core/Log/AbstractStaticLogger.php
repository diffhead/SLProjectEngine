<?php namespace Core\Log;

use Interfaces\Logger;
use Interfaces\LoggerStatic;

abstract class AbstractStaticLogger implements LoggerStatic
{
    protected static string  $logFile     = 'default';
    protected static bool    $logFileDate = true;

    protected static ?Logger $instance = null;

    public static function getFilePath(): string
    {
        static $logPath;

        if ( empty($logPath) === false ) {
            return $logPath;
        }

        $logPath = _APP_BASE_DIR_ . 'log/' . static::$logFile;

        if ( static::$logFileDate ) {
            $logPath .= '_' . date('dmY');
        }

        $logPath .= '.log';

        return $logPath;
    }

    public static function isFileWithDate(): bool
    {
        return static::$logFileDate;
    }

    public static function getInstance(): Logger
    {
        if ( self::$instance === null ) {
            self::$instance = new FileLog(static::getFilePath());
        }

        return self::$instance;
    }

    public static function log(string $message, int $level = E_NOTICE): bool
    {
        return self::getInstance()->log($message, $level);
    }

    public static function notice(string $message): bool
    {
        return self::getInstance()->notice($message);
    }

    public static function warning(string $message): bool
    {
        return self::getInstance()->warning($message);
    }

    public static function error(string $message): bool
    {
        return self::getInstance()->error($message);
    }
}
