<?php namespace Core\Path;

use DateTime;

use Interfaces\CollectionItem;

class File implements CollectionItem
{
    protected int      $size = 0;
    protected string   $name = '';
    protected string   $type = '';
    protected string   $path = '';
    protected string   $owner = '';
    protected bool     $exists = false;
    protected bool     $isFile = true;

    protected ?DateTime $mtime = null;
    protected ?DateTime $ctime = null;

    public function __construct(string $pathToFile)
    {
        $this->initFile($pathToFile);
    }

    protected function initFile(string $fileToPath): void
    {
        $this->initPath($fileToPath);

        if ( $this->exists ) {
            $stats = stat($this->path);
        }

        if ( $this->exists && $stats ) {
            $this->size = $stats['size'];
            $this->name = basename($this->path);
            $this->type = filetype($this->path) ?: '';
            $this->owner = fileowner($this->path) ?: '';
            $this->mtime = new DateTime(date('Y-m-d H:i:s', $stats['mtime']));
            $this->ctime = new DateTime(date('Y-m-d H:i:s', $stats['ctime']));
            $this->isFile = is_dir($this->path) === false;
        }
    }

    private function initPath(string $path): void
    {
        if ( file_exists($path) ) {
            $this->exists = true;
        }

        $this->path = $path;
    }

    public function getValue(string $prop): mixed
    {
        return $this->$prop;
    }

    public function getUniqueId(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getModifiedTime(): ?DateTime
    {
        return $this->mtime;
    }

    public function getCreateTime(): ?DateTime
    {
        return $this->ctime;
    }

    public function isExists(): bool
    {
        return $this->exists;
    }

    public function isFile(): bool
    {
        return $this->isFile;
    }

    public function create(): bool
    {
        $status = touch($this->path);

        if ( $status ) {
            $this->initFile($this->path);
        }

        return $status;
    }

    public function delete(): bool
    {
        return unlink($this->path);
    }
}
