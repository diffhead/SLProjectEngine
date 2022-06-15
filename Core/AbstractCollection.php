<?php namespace Core;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

use Interfaces\Collection;
use Interfaces\CollectionItem;

use Services\ClassService;

abstract class AbstractCollection implements Collection, IteratorAggregate
{
    protected static string $collectionItemClass = '';

    public static function getCollectionItemClass(): string
    {
        return static::$collectionItemClass;
    }

    protected array $collectionItems = [];

    public function __construct(array $items = [])
    {
        foreach ( $items as $item ) {
            if ( $item instanceof CollectionItem && ClassService::isClass($item, static::$collectionItemClass) ) {
                $this->putItemIntoCollection($item);
            }
        }
    }

    public function getItemByUniqueId(string $uniqueId): ?CollectionItem
    {
        foreach ( $this->collectionItems as $item ) {
            if ( $item->getUniqueId() === $uniqueId ) {
                return $item;
            }
        }

        return null;
    }

    public function getItemByPropEquals(string $prop, mixed $value): ?CollectionItem
    {
        foreach ( $this->collectionItems as $item ) {
            if ( $item->getValue($prop) === $value ) {
                return $item;
            }
        }

        return null;
    }

    public function getItemByPropNotNull(string $prop): ?CollectionItem
    {
        foreach ( $this->collectionItems as $item ) {
            if ( $item->getValue($prop) !== null ) {
                return $item;
            }
        }

        return null;
    }

    public function getItemByIndex(int $index): ?CollectionItem
    {
        if ( isset($this->collectionItems[$index]) ) {
            return $this->collectionItems[$index];
        }

        return null;
    }

    public function putItemIntoCollection(CollectionItem $item): bool
    {
        if ( ClassService::isClass($item, static::$collectionItemClass) ) {
            $this->collectionItems[] = $item;

            return true;
        }

        return false;
    }

    public function deleteItemByIndex(int $index): bool
    {
        $items = [];
        $itemFound = false;

        foreach ( $this->collectionItems as $itemIndex => $item ) {
            if ( $itemIndex === $index ) {
                $itemFound = true;
            } else {
                $items[] = $item;
            }
        }

        $this->collectionItems = $items;

        return $itemFound;
    }

    public function deleteItemByUniqueId(string $uid): bool
    {
        $items = [];
        $itemFound = false;

        foreach ( $this->collectionItems as $item ) {
            if ( $item->getUniqueId() === $uid ) {
                $itemFound = true;
            } else {
                $items[] = $item;
            }
        }

        $this->collectionItems = $items;

        return $itemFound;
    }

    public function length(): int
    {
        return count($this->collectionItems);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->collectionItems);
    }
}
