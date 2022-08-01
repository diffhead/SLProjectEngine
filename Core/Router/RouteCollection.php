<?php namespace Core\Router;

use Core\AbstractCollection;

class RouteCollection extends AbstractCollection
{
    protected static string $collectionItemClass = '\\Core\\Router\\Route';
}
