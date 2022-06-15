<?php namespace Interfaces;

interface CollectionItem
{
    public function getValue(string $property): mixed;
    public function getUniqueId(): string;
}
