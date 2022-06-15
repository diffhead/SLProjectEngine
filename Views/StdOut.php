<?php namespace Views;

use DateTime;

use Core\View;

class StdOut extends View
{
    protected string $template = 'templates/stdout.tpl';
    protected bool   $templateIsFile = true;

    public function __construct(int $code = 0, string $message = '')
    {
        parent::__construct();

        $this->setCode($code);
        $this->setMessage($message);
    }

    public function render(): string
    {
        $this->assign([ 
            'time' => (new DateTime())->format('Y-m-d H:i') 
        ]);

        return parent::render();
    }

    public function setCode(int $code = 0): void
    {
        $this->assign([ 
            'code' => $code 
        ]);
    }

    public function setMessage(string $message = ''): void
    {
        $this->assign([ 
            'message' => $message 
        ]);
    }
}
