<?php namespace Core\Path;

class Directory extends File
{
    private FileCollection      $dirItems;
    private FileCollection      $dirFiles;
    private DirectoryCollection $dirDirectories;

    private bool $isDir = false;

    public function __construct(string $pathToDir, bool $scan = true)
    {
        parent::__construct($pathToDir);

        $this->isDir = is_dir($pathToDir);
        $this->initDirItems($scan);
    }

    private function initDirItems(bool $scan): void
    {
        $this->dirItems = new FileCollection();
        $this->dirFiles = new FileCollection();
        $this->dirDirectories = new DirectoryCollection();

        if ( $scan && $this->isDir ) {
            $dirItems = array_diff(scandir($this->path), ['.', '..']);

            foreach ( $dirItems as $item ) {
                $itemPath = $this->path . '/' . $item;
                $itemIsDir = is_dir($itemPath);
                $itemObject = $itemIsDir ? new Directory($itemPath) : new File($itemPath);

                if ( $itemIsDir ) {
                    $this->dirDirectories->putItemIntoCollection($itemObject);
                } else {
                    $this->dirFiles->putItemIntoCollection($itemObject);
                }

                $this->dirItems->putItemIntoCollection($itemObject);
            }
        }
    }

    public function delete(): bool
    {
        if ( $this->isExists() === false ) {
            return false;
        }

        foreach ( $this->dirItems as $item ) {
            $itemUid = $item->getUniqueId();

            if ( $item->delete() ) {
                $this->dirItems->deleteItemByUniqueId($itemUid);

                if ( $item->isFile() ) {
                    $this->dirFiles->deleteItemByUniqueId($itemUid);
                } else {
                    $this->dirDirectories->deleteItemByUniqueId($itemUid);
                }
            }
        }

        if ( $this->dirItems->length() ) {
            return false;
        }

        if ( rmdir($this->path) === false ) {
            return false;
        }

        $this->exists = true;

        return true;
    }

    public function create(): bool
    {
        if ( $this->isExists() === false ) {
            $status = mkdir($this->path);

            if ( $status ) {
                $this->initFile($this->path);
            }

            return $status;
        }

        return false;
    }

    public function putFileIntoCurrent(File $file): void
    {
        $this->dirItems->putItemIntoCollection($file);
        $this->dirFiles->putItemIntoCollection($file);
    }

    public function putDirectoryIntoCurrent(Directory $dir): void
    {
        $this->dirItems->putItemIntoCollection($dir);
        $this->dirDirectories->putItemIntoCollection($dir);
    }

    public function isEmpty(): bool
    {
        return $this->dirItems->length() === 0;
    }
}
