<?php namespace Core;

class Router
{
    const SET_ROUTE_FAILED = 0;
    const SET_ROUTE_SUCCESS = 1;
    const SET_ROUTE_CHANGED = -1;

    private array $routes = [];

    public function isRouteExists(string $route): bool
    {
        return isset($this->routes[$route]);
    }

    public function setRoute(Route $route): int
    {
        $status = self::SET_ROUTE_SUCCESS;

        if ( $this->isRouteExists($route->getRoute()) ) {
            if ( $this->getRoute($route->getRoute())->isProtected() ) {
                return self::SET_ROUTE_FAILED;
            }

            $status = self::SET_ROUTE_CHANGED;
        }

        $this->routes[] = $route;

        return $status;
    }

    public function getRoute(string $routeStr): null|Route
    {
        static $matches = [];

        if ( isset($matches[$routeStr]) ) {
            return $matches[$routeStr];
        }

        foreach ( $this->routes as $route ) {
            if ( $route->isCurrentRoute($routeStr) ) {
                $matches[$routeStr] = $route;

                return $route;
            }
        }

        return null;
    }
}
