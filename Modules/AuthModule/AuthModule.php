<?php namespace Modules\AuthModule;

use Core\RouterProvider;
use Core\AbstractModule;

class AuthModule extends AbstractModule
{
    public function init(): void
    {
        $routerProvider = new RouterProvider();
        $routerProvider->setRoutesFromJsonFile($this->path . 'routes.json');
    }
}
