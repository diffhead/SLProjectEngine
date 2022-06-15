<?php namespace Core\Log;

class ApplicationLogger extends AbstractStaticLogger
{
    protected static string  $logFile     = 'application';
    protected static bool    $logFileDate = true;
}
