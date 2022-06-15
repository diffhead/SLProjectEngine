<?php namespace Web\Controller;

use Core\Controller;

use Web\HttpHeader;

use Views\Error as ErrorView;

use Services\HttpService;

use Config\ApplicationConfig;

class Error extends Controller
{
    private int    $code;
    private string $message;
    private string $additionalMessage;

    public function __construct(int $code, string $message, string $additionalMessage = '')
    {
        $this->code = $code;

        $this->message = $message;
        $this->additionalMessage = $additionalMessage;

        if ( ApplicationConfig::get('dev') === false && $this->additionalMessage !== '' ) {
            $this->additionalMessage = '';
        }
    }

    public function init(): void
    {
        $this->view = new ErrorView($this->code, $this->message, $this->additionalMessage);
    }

    public function execute(array $params = []): bool
    {
        HttpService::setResponseCode($this->code);
        HttpService::setResponseHeader(new HttpHeader('Content-Type', 'text/html'));

        return true;
    }
}
