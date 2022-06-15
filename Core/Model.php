<?php namespace Core;

use JsonSerializable;

use ReflectionClass;
use ReflectionProperty;

use Services\ArrayService;
use Services\JsonService;

class Model extends ActiveRecord implements JsonSerializable
{
    public function __construct(int $id = 0)
    {
        if ( $id ) {
            $modelData = static::where($this->getAloneItemWhereStatement($id));

            if ( ArrayService::isEmpty($modelData) === false ) {
                $this->setModelData(ArrayService::pop($modelData));
            }
        }
    }

    private function getAloneItemWhereStatement(int $primaryFieldValue): string
    {
        $idField = static::$idField;

        return "{$idField}='{$primaryFieldValue}'";
    }

    public function setModelData(array $properties = []): void
    {
        foreach ( $properties as $property => $value ) {
            $this->__set($property, $value);
        }
    }

    public function isValidModel(): bool
    {
        $properties = ArrayService::getKeys(static::$definitions);

        foreach ( $properties as $property ) {
            if ( isset($this->$property) === false ) {
                return false;
            }
        }

        return true;
    }

    public function jsonSerialize(): mixed
    {
        $reflectionClass = new ReflectionClass($this);
        $reflectionProperties = $reflectionClass->getProperties();

        $modelData = [];

        foreach ( $reflectionProperties as $property ) {
            if ( $property->isStatic() === false && $property->isProtected() && $property->isInitialized($this) ) {
                $modelData[$property->getName()] = $property->getValue($this);
            }
        }

        return $modelData;
    }
}
