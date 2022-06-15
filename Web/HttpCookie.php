<?php namespace Web;

class HttpCookie
{
    private string $name;
    private string $value;
    private int    $expries;
    private string $path;
    private string $domain;
    private bool   $secure;
    private bool   $httpOnly;

    public function __construct(
        string $name, 
        string $value, 
        int $expires = -1, 
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ) {
        $this->name = $name;
        $this->value = $value;

        if ( $expires === -1 ) {
            $this->expires = 0;
        } else {
            $this->expires = $expires;
        }

        $this->expires = $expires;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function getExpiresRFC(): string
    {
        $expires = new DateTime();
        $expires->setTimestamp($this->expires);

        return $expires->format(DateTimeInterface::RFC7231);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getSecure(): bool
    {
        return $this->secure;
    }

    public function getHttpOnly(): bool
    {
        return $this->httpOnly;
    }
}
