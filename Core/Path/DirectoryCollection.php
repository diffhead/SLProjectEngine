<?php namespace Core\Path;

use Core\AbstractCollection;

class DirectoryCollection extends AbstractCollection
{
    protected static string $collectionItemClass = '\\Core\\Path\\Directory';
}
