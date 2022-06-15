<?php namespace Services;

class MockService
{
    public static function getRoutes(): array
    {
        return [
            '/' => [
                'type' => 1,
                'controller' => '\\Web\\Controller\\Index',
                'params' => [ 'GET', 'POST' ],
                'route' => '/'
            ]
        ];
    }

    public static function getModules(): array
    {
        return [
            [
                'enable' => true,
                'name' => 'firstModule'
            ],
            [
                'enable' => false,
                'name' => 'secondModule'
            ],
            [
                'enable' => false, 
                'name' => 'thirdModule'
            ],
            [
                'enable' => true,
                'name' => 'fourthModule'
            ]
        ];
    }
}
