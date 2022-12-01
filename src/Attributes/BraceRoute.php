<?php

namespace Brace\Router\Attributes;

use Brace\Core\Base\BraceAbstractMiddleware;
use Brace\Router\Router;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BraceRoute
{

    public function __construct(
        /**
         * The Route string
         *
         * POST@some/{parameter}
         */
        public string $route,

        /**
         * Unique Name of the route
         *
         * @var string|null
         */
        public ?string $name = null,

        /**
         * Middleware to use for this (is extended to middleware)
         *
         * @var BraceAbstractMiddleware[]
         */
        public array $mw = []
    ) {

    }


    /**
     * Called by Router::delegate() during initialisation
     *
     * @param string $className
     * @param string $methodName
     * @param Router $router
     * @param array $mw
     * @param string $mount
     * @return void
     */
    public function __registerRoute(string $className, string $methodName, Router $router, array $mw=[], string $mount="") {
        [$method, $route] = explode("@", $this->route, 2);
        $routeActual = $method . "@" . $mount . $this->route;

        foreach ($this->mw as $curMw) {
            $mw[] = $curMw;
        }

        $router->on($routeActual, [$className, $methodName], $mw, $this->name);
    }

}
