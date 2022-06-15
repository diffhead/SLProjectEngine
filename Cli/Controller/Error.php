<?php namespace Cli\Controller;

use Core\Controller;

use Views\StdOut;

class Error extends Controller
{
    private int    $code = 0;
    private string $message = '';

    public function __construct(int $code, $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function init(): void
    {
        $this->view = new StdOut($this->code, $this->message);
    }
}
