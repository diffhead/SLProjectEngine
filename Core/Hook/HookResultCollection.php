<?php namespace Core\Hook;

use Core\AbstractCollection;

class HookResultCollection extends AbstractCollection
{
    protected static string $collectionItemClass = '\\Core\\Hook\\HookResult';
}
