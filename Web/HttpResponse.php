<?php namespace Web;

class HttpResponse
{
    private string|array $data = [];
    private array        $headers = [];
    private array        $cookies = [];
    private int          $code = 0;
    private bool         $status = false;

    public function __construct(
        int $code, string|array $data = [], array $headers = [], array $cookies = [], bool $status = true
    ) {
        $this->code = $code;
        $this->data = $data;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->status = $status;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getData(): string|array
    {
        return $this->data;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }
}
