<?php namespace Core\Path;

use Core\AbstractCollection;

class FileCollection extends AbstractCollection
{
    protected static string $collectionItemClass = '\\Core\\Path\\File';
}
