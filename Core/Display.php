<?php namespace Core;

class Display
{
    private bool $buffer = false;

    public function __construct() 
    {
        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);
    }

    public function echo(): void
    {
        $content = ob_get_clean();

        echo $content;
    }
}
