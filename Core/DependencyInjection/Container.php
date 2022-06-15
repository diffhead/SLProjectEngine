<?php namespace Core\DependencyInjection;

use Exception;
use ReflectionClass;

use Services\DependencyInjectionService;

class Container
{
    private string $class;
    private array  $constructorArgs;

    public function __construct(string $class, array $constructorArgs)
    {
        $this->class = $class;
        $this->constructorArgs = $constructorArgs;
    }

    public function get(string $entity): Object
    {
        if ( isset($this->constructorArgs[$entity]) ) {
            $constructorArgs = DependencyInjectionService::executeActions($this->constructorArgs[$entity]);
            $reflectionClass = new ReflectionClass($this->class);

            return $reflectionClass->newInstanceArgs($constructorArgs);
        } else {
            throw new Exception("Entity '$entity' doesnt exist in this container");
        }
    }
}
