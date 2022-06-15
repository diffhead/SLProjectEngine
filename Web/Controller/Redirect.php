<?php namespace Web\Controller;

use Core\Controller;

use Web\HttpHeader;

use Services\HttpService;

class Redirect extends Controller
{
    private int    $code;
    private string $target;

    public function __construct(int $code, string $target)
    {
        $this->code = $code;
        $this->target = $target;
    }

    public function execute(array $params = []): bool
    {
        $redirectHeader = new HttpHeader('Location', $this->target);

        HttpService::setResponseCode($this->code);
        HttpService::setResponseHeader($redirectHeader);

        return true;
    }
}
