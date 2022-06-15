<?php namespace Web;

class HttpHeader
{
    private string $name = '';
    private string $value = '';
    private bool   $replace = true;

    public function __construct(string $name, string $value, bool $replace = true)
    {
        $this->name = $name;
        $this->value = $value;
        $this->replace = $replace;
    }

    public function getRaw(): string
    {
        return "{$this->name}: {$this->value}";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isForReplace(): bool
    {
        return $this->replace;
    }
}
