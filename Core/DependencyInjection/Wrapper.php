<?php namespace Core\DependencyInjection;

class Wrapper
{
    private Object $wrapped;

    public function __construct(Object $obj)
    {
        $this->wrapped = $obj;
    }

    public function getWrappedObject(): Object
    {
        return $this->wrapped;
    }
}
