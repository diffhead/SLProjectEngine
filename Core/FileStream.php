<?php namespace Core;

use Generator;

use Core\Path\File;

use Services\FileService;

class FileStream
{
    public const ACCESS_RW = 'r+';
    public const ACCESS_WO = 'w';
    public const ACCESS_RO = 'r';
    public const ACCESS_AW = 'a';
    public const ACCESS_AR = 'a+';

    private File   $file;
    private string $access;
    private mixed  $stream = null;
    private bool   $phpStream = false;

    public function __construct(string $filePath, string $access = FileStream::ACCESS_RO, bool $phpStream = false)
    {
        $this->file = new File($filePath);

        $this->access = $access;
        $this->phpStream = $phpStream;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function getFilePath(): string
    {
        return $this->file->getPath();
    }

    public function getFileSize(): int
    {
        if ( $this->stream )  {
            if ( $this->phpStream ) {
                $streamSize = (int)stream_get_meta_data($this->stream)['unread_bytes'];

                return $streamSize ?: _PHP_INPUT_MAX_LENGTH_;
            }

            return $this->file->getSize();
        }

        return 0;
    }

    public function open(): bool
    {
        if ( $this->phpStream || $this->file->isExists() ) {
            $this->stream = fopen($this->file->getPath(), $this->access);

            return (bool)$this->stream;
        }

        return false;
    }

    public function touch(): bool
    {
        return $this->file->create();
    }

    public function close(): void
    {
        if ( $this->stream ) {
            fclose($this->stream);

            $this->stream = null;
        }
    }

    public function read(): string
    {
        if ( $this->stream && $this->isAvailableForReading() && $fileSize = $this->getFileSize() ) {
            return fread($this->stream, $fileSize);
        }

        return '';
    }

    public function write(string $text): bool
    {
        if ( $this->stream && $this->isAvailableForWriting() ) {
            return fwrite($this->stream, $text);
        }

        return false;
    }

    public function isAvailableForReading(): bool
    {
        return $this->access === self::ACCESS_RW || 
               $this->access === self::ACCESS_RO || 
               $this->access === self::ACCESS_AW ||
               $this->access === self::ACCESS_AR;
    }

    public function isAvailableForWriting(): bool
    {
        return $this->access === self::ACCESS_RW || 
               $this->access === self::ACCESS_WO ||
               $this->access === self::ACCESS_AW ||
               $this->access === self::ACCESS_AR;
    }

    public function getLines(): Generator
    {
        if ( $this->stream ) {
            while ( ($line = fgets($this->stream)) !== false ) {
                yield $line;
            }
        }

        return '';
    }
}
