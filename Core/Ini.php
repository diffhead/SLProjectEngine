<?php namespace Core;

class Ini
{
    private string $name  = '';
    private string $value = '';
    private bool   $inited = false;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->initValue();
    }

    private function initValue(): void
    {
        if ( empty($this->name) ) {
            $this->value = '';
        } else {
            $this->value = (string)($gotIt = ini_get($this->name));

            if ( $gotIt ) {
                $this->inited = true;
            }
        }
    }

    private function isInited(): bool
    {
        return $this->inited;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): bool
    {
        if ( ini_set($this->name, $value) !== false ) {
            $this->value = $value;

            return true;
        }

        return false;
    }
}
