<?php namespace Core\Hook;

use Interfaces\CollectionItem;

class HookResult implements CollectionItem
{
    public const ERROR_NONE = 0;
    public const ERROR_METHOD_CALL_FAILED = 1;
    public const ERROR_METHOD_NOT_EXISTS = 2;

    private string $hook;
    private string $sign;
    private bool   $success = true;
    private mixed  $hookData = null;
    private int    $errorCode = 0;

    public function __construct(string $hook, string $sign = '')
    {
        $this->hook = $hook;
        $this->sign = $sign;
    }

    public function getUniqueId(): string
    {
        return $this->sign;
    }

    public function getValue(string $prop): mixed
    {
        return $this->$prop;
    }

    public function setFailed(): void
    {
        $this->success = false;
    }

    public function setSuccess(): void
    {
        $this->success = true;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setData(mixed $data): void
    {
        $this->hookData = $data;
    }

    public function getData(): mixed
    {
        return $this->hookData;
    }

    public function getHookName(): string
    {
        return $this->hook;
    }

    public function setErrorCode(int $code): void
    {
        $this->errorCode = $code;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
