<?php namespace Core\DependencyInjection;

use Exception;

class Action
{
    private static array $availableTypes = [
        'class'         => true,
        'static-method' => true,
        'static-value'  => true
    ];

    private string $type;
    private mixed  $value;
    private array  $arguments;

    public function  __construct(string $type, mixed $value, array $arguments = [])
    {
        if ( isset(self::$availableTypes[$type]) === false ) {
            throw new Exception("Current DI Action type '$type' doesnt exist.");
        }

        $this->type = $type;
        $this->value = $value;
        $this->arguments = $arguments;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
