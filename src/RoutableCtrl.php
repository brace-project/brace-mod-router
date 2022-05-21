<?php

namespace Brace\Router;

interface RoutableCtrl
{

    /**
     * Let the Controller define its routes itself
     *
     * This method is called from Router
     *
     * @param Router $router
     * @param string $mount
     * @param array $mw
     * @return void
     */
    public static function Routes(Router $router, string $mount, array $mw) : void;
}
