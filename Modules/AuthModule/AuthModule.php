<?php namespace Modules\AuthModule;

use Core\AbstractModule;

use Core\Router\Route;
use Core\Router\RouteCollection;

class AuthModule extends AbstractModule
{
    public function registerRoutes(): RouteCollection
    {
        return new RouteCollection([
            new Route('/api/login', Route::TYPE_RAW, '\\Modules\\AuthModule\\Controller\\Login', [ 'POST' ], true, true),
            new Route('/api/logout', Route::TYPE_RAW, '\\Modules\\AuthModule\\Controller\\Logout', [ 'GET' ], true, true),
            new Route('/api/register', Route::TYPE_RAW, '\\Modules\\AuthModule\\Controller\\Register', [ 'POST' ], true, true)
        ]);
    }
}
