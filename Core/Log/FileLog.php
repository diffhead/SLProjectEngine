<?php namespace Core\Log;

use DateTime;
use Exception;

use Core\Path\File;
use Core\FileStream;

use Interfaces\Logger;

class FileLog implements Logger
{
    private File        $file;
    private ?FileStream $fileStream = null;
    private int         $maxLoggingLevel;

    private static array $levelString = [
        E_NOTICE  => "Notice",
        E_WARNING => "Warning",
        E_ERROR   => "Fatal Error"
    ];

    public function __construct(string $filePath, int $maxLoggingLevel = E_ALL)
    {
        $this->file = new File($filePath);

        if ( $this->file->isExists() ) {
            if ( $this->file->isFile() === false ) {
                throw new Exception("$filePath is not a file object");
            }

            $this->openFileStream();
        } else {
            if ( $this->file->create() === false ) {
                throw new Exception("Failed $filePath creation");
            }

            $this->openFileStream();
        }

        $this->maxLoggingLevel = $maxLoggingLevel;
    }

    private function openFileStream(): void
    {
        $this->fileStream = new FileStream($this->file->getPath(), FileStream::ACCESS_AR);
        $this->fileStream->open();
    }

    public function log(string $message, int $level = E_NOTICE): bool
    {
        if ( $level > $this->maxLoggingLevel || $this->fileStream === null ) {
            return false;
        }

        $currentTime = (new DateTime())->format('Y-m-d H:i:s');
        $currentLevel = self::getErrorLevelString($level);
 
        $fullMessage = "{$currentTime} - {$currentLevel}: $message\n";

        return $this->fileStream->write($fullMessage);
    }

    public function notice(string $message): bool
    {
        return $this->log($message, E_NOTICE);
    }

    public function warning(string $message): bool
    {
        return $this->log($message, E_WARNING);
    }

    public function error(string $message): bool
    {
        return $this->log($message, E_WARNING);
    }

    private static function getErrorLevelString(int $level): string
    {
        if ( isset(self::$levelString[$level]) ) {
            return self::$levelString[$level];
        }

        return self::$levelString[E_NOTICE];
    }

    public function __destruct()
    {
        if ( isset($this->fileStream) ) {
            $this->fileStream->close();
        }
    }
}
