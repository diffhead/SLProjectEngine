<?php namespace Web;

use Interfaces\ApplicationRequest;

class HttpRequest implements ApplicationRequest
{
    private string       $url         = '';
    private string       $method      = 'GET';
    private  array       $headers     = [];
    private  array       $cookies     = [];
    private string|array $data        = [];
    private  array       $requestData = [];

    public function __construct(
        string $url, 
        string $method     = 'GET', 
         array $headers    = [], 
         array $cookies    = [],
        string|array $data = []
    )
    {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->data = $data;

        $this->setRequestData([
            'url'     => $url,
            'method'  => $method,
            'headers' => $headers,
            'cookies' => $cookies,
            'data'    => $data
        ]);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setRequestData(array $requestData): bool
    {
        if ( empty($this->requestData) === true ) {
            $this->requestData = $requestData;

            return true;
        }

        return false;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }
}
