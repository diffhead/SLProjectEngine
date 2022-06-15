<?php namespace Core;

use Interfaces\Singleton;
use Interfaces\User;
use Interfaces\ApplicationRequest;

use Core\Controller;
use Core\Application;

use Services\ClassService;

class Context implements Singleton
{
    private static Context $_INSTANCE;

    private User               $user;
    private Controller         $controller;
    private Application        $application;
    private ApplicationRequest $applicationRequest;

    public static function setInstance(Singleton $instance): bool
    {
        if ( isset(self::$_INSTANCE) === false ) {
            self::$_INSTANCE = $instance;

            return true;
        }

        return false;
    }

    public static function getInstance(): self
    {
        if ( isset(self::$_INSTANCE) === false ) {
            self::setInstance(new self());
        }

        return self::$_INSTANCE;
    }

    public function __construct(
        Application        $application, 
        ApplicationRequest $applicationRequest
    )
    {
        $this->application = $application;
        $this->applicationRequest = $applicationRequest;
    }

    public function __set(string $property, mixed $value): void
    {
        if ( ClassService::propertyExists($this, $property) ) {
            $this->$property = $value;
        }
    }

    public function __get(string $property): mixed
    {
        return $this->$property;
    }
}
