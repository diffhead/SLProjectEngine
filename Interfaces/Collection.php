<?php namespace Interfaces;

interface Collection
{
    public static function getCollectionItemClass(): string;

    public function __construct(array $items = []);

    public function getItemByUniqueId(string $uniqueId): ?CollectionItem;
    public function getItemByPropEquals(string $prop, mixed $value): ?CollectionItem;
    public function getItemByPropNotNull(string $prop): ?CollectionItem;
    public function getItemByIndex(int $index): ?CollectionItem;

    public function putItemIntoCollection(CollectionItem $item): bool;

    public function deleteItemByUniqueId(string $uid): bool;
    public function deleteItemByIndex(int $index): bool;

    public function length(): int;
}
