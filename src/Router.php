<?php


namespace Brace\Router;


use Brace\Core\BraceApp;
use Brace\Core\EnvironmentType;
use Brace\Core\Helper\ClassFileParser;
use Brace\Router\Attributes\BraceRoute;
use Brace\Router\Type\Route;
use Brace\Router\Type\RouteParams;
use Brace\Router\Type\UndefinedRoute;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Router
{

    private $routes = [];

    private string $routePrefix = "";

    public function __construct(protected BraceApp $app) {

    }


    /**
     * For this to work use AdjustRoutePrefixMiddleware
     * 
     * @param string $routePrefix
     * @return $this
     */
    public function setRoutePrefix(string $routePrefix) : self
    {
        $this->routePrefix = $routePrefix;
        return $this;
    }
    

    public function getRoutePrefix() : string
    {
        return $this->routePrefix;
    }

    public function delegate(string $route, string $className, array $mw=[]) : self
    {
        $this->routes[] = ["route"=>$route, "delegate"=>$className, "mw" => $mw];
        return $this;
    }


    public function debugGetRoutes($return = false) : ?array{
        $debug = [];
        foreach ($this->routes as $route) {
            $debug[] = [
                "route" => $route["route"],
                "match" => RouteMatcher::BuildPreg($route["route"])
            ];
        }
        if ($return === false) {

            print_r($debug);
            return null;
        }
        return $debug;
    }


    /**
     * Autoload Controller Files
     *
     *
     * @param string $mountPoint    e.g. "/api" or "" if none
     * @param string $path          e.g. __DIR__ . "/../src/Ctrl/*.php"
     * @param array $mw             Middlewares to apply to all routes
     * @return void
     */
    public function autoload(string $mountPoint, string $path, array $mw = []) : void {
        $files = glob($path);
        $numberLoaded = 0;
        foreach ($files as $file) {
            if ( ! endsWith($file, ".php"))
                continue;
            $classNames = ClassFileParser::extractPhpClasses($file);
            if ($classNames === null)
                continue;

            foreach ($classNames as $curClassName) {
                $numberLoaded++;
                $this->registerClass($mountPoint, $curClassName, $mw);
            }
        }
        if ($numberLoaded === 0)
            throw new \InvalidArgumentException("No Controllers found in '$path'");
    }

    /**
     * Register a Class implementing RoutableCtrl or has public
     *
     * @param string $mountPoint
     * @param class-string $className
     * @param array $mw
     * @return void
     */
    public function registerClass(string $mountPoint, string $className, array $mw=[]) {
        if (is_subclass_of($className, RoutableCtrl::class)) {
            $className::Routes($this, $mountPoint, $mw);
            return;
        }

        $reflection = new \ReflectionClass($className);
        foreach ($reflection->getMethods() as $method) {
            $attrs = $method->getAttributes(BraceRoute::class);
            if (count($attrs) === 0)
                continue;
            if ( ! $method->isPublic()) {
                throw new \InvalidArgumentException("Attribute 'BraceRoute' defined on private method: " . $className . "::" . $method . "() should be public!");
            }
            $curAttr = $attrs[0]->newInstance();
            assert($curAttr instanceof BraceRoute);
            $curAttr->__registerRoute($reflection->name, $method->name, $this, $mw, $mountPoint);
        }
    }


    /**
     * Return array containing all Routes in chronological order.
     *
     * @return string[]
     */
    public function dumpRoutes() : array {
        $ret = [];
        foreach ($this->routes as $route) {

            $ret[] = $route["route"] . " => " . phore_debug_type($route["call"]) . " (" . $route["name"] . ")";
        }
        return $ret;
    }

    /**
     *
     * Parameter3: Can be a instance of MiddlewareInterface, a Class-String or a name of a dependency
     *
     * @param string $route
     * @param callable|class-string $fn
     * @param MiddlewareInterface[]|class-string[] $mw
     * @return $this
     */
    public function on(string $route, callable|string|array $fn, array $mw = [], string $name=null) : self
    {
        if (is_string($fn) && ! class_exists($fn))
            throw new \InvalidArgumentException("Parameter 2 must be Closure or class-string: '$fn' class not existing");

        $this->routes[] = ["route"=>$route, "call" => $fn, "mw" => $mw, "name"=>$name];
        return $this;
    }


    public function getJSStub () : string {
        $data = [];
        foreach ($this->routes as $curRoute) {
            if ($curRoute["name"] === null)
                continue;
            [$methods, $route] = explode("@", $curRoute["route"]);
            foreach (explode("|", $methods) as $method) {

                $data[$curRoute["name"] . "_" . $method] = preg_replace_callback("/::?([a-zA-Z0-9\-\_]+)/", fn($match) => "{" . $match[1] . "}", $method . "@" . $route );
            }
        }
        return "// Autogenerated file by brace/mod-router - do not change manually!\nexport const ROUTE_PREFIX = \"%%ROUTE_PREFIX%%\";\nexport const API = " . phore_json_encode($data, prettyPrint: true) . ";";
    }

    public function writeJSStub(string $fileName) {
        if ($this->app->environmentType !== EnvironmentType::DEVELOPMENT)
            return; // Only write stubs in development mode
        $content = $this->getJSStub();
        $file = phore_file($fileName);
        if ($file->get_contents() !== $content)
            $file->set_contents($content);
    }


    /**
     * @param ServerRequestInterface $serverRequest
     * @return Route
     */
    public function _evalRoute(ServerRequestInterface $serverRequest) : Route
    {

        foreach ($this->routes as $curRoute) {
            $routeParams = [];
            if ( ! RouteMatcher::IsMatching($curRoute["route"], $serverRequest, $routeParams, $methods)) {
                continue;
            }

            return new Route(
                $curRoute["route"],
                $serverRequest->getUri()->getPath(),
                $serverRequest->getMethod(),
                new RouteParams($routeParams),
                $curRoute["call"],
                $curRoute["mw"]
            );
        }
        return new UndefinedRoute($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
    }

}
