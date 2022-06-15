<?php namespace Views;

use Core\View;

use Services\StringService;

class Error extends View
{
    protected string $template = 'templates/error.tpl';

    protected array  $params = [
        'code'       => 0,
        'message'    => _APP_EMPTY_STRING_,
        'extMessage' => _APP_EMPTY_STRING_ 
    ];

    protected bool   $templateIsFile = true;

    public function __construct(int $code, string $message = 'Error', string $extMessage = _APP_EMPTY_STRING_)
    {
        parent::__construct();

        $this->assign([
            'code'       => $code,
            'message'    => $message,
            'extMessage' => $extMessage
        ]);
    }
}
