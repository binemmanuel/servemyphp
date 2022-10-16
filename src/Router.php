<?php

namespace Binemmanuel\ServeMyPhp;

use mysqli;
use PDO;

class Router
{
    private array $routes;
    private static mysqli|PDO $database;

    function __construct(mysqli|PDO $database)
    {
        static::$database = $database;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Matches a route to a request method
     */
    private function matchRequest(
        String $method,
        String $route,
        callable|array $callback,
        callable|array|null $next,
    ): self {
        $this->routes[$method][$route][] = $callback;
        $this->routes[$method][$route][] = $next;

        return $this;
    }

    /**
     * Match more than on request
     * method to a route
     */
    public function match(
        array $methods,
        String $route,
        callable|array $callback,
        callable|array $next,

    ) {
        foreach ($methods as $method) {
            $this->matchRequest(
                $method,
                $route,
                $callback,
                $next
            );
        }
        return $this;
    }

    /**
     * Create a get route
     */
    public function get(
        String $route,
        callable|array $callback,
        callable|array $next = null,
    ): self {
        return $this->matchRequest(
            'get',
            $route,
            $callback,
            $next,
        );
    }

    /**
     * Create a post route
     */
    public function post(
        String $route,
        callable|array $callback,
        callable|array $next = null,
    ): self {
        return $this->matchRequest(
            'post',
            $route,
            $callback,
            $next
        );
    }

    /**
     * Create a delete route
     */
    public function delete(
        String $route,
        callable|array $callback,
        callable|array $next = null,
    ): self {
        return $this->matchRequest(
            'delete',
            $route,
            $callback,
            $next
        );
    }

    public function redirect(
        String $route,
        String $newRoute,
    ) {


        return $this;
    }

    /**
     * Listen to the route the user is visiting
     */
    public function run(): void
    {
        // Get the route the user is visiting
        $requestedRoute = explode('?', self::sanitize($_SERVER['REQUEST_URI']))[0] ?? '';

        $requestedRoute = self::removeTrailingSlash($requestedRoute);

        // Sanitize and change request method to lowercase 
        $method = self::sanitize(strtolower($_SERVER['REQUEST_METHOD']));

        // Get the action that should be performed
        [$callback, $next] = $this->routes[$method][$requestedRoute] ?? null;

        if (is_callable($callback)) {
            call_user_func($callback, new Request, new Response, $next);
            return;
        }

        if (is_iterable($callback)) {
            [$controller, $method, $next] = $callback;

            // Instantiate the controller Object and
            // call the required method
            (new $controller(static::$database))->$method(new Request, new Response);

            return;
        }

        Response::sendJson(['message' => 'Welcome API']);
    }

    private static function removeTrailingSlash(String $string): String
    {
        $lastChar = $string[strlen($string) - 1] ?? '';

        if ($lastChar === '/') {
            $tempString = '';

            for ($i = 0; $i < strlen($string); $i++) {
                if ((strlen($string) - 1) === $i) continue;

                $tempString .= $string[$i];
            }

            $string = $tempString;
        }
        return $string === "" ? '/' : $string;
    }

    private static function sanitize(String|array $data): String|array
    {
        if (is_array($data)) {
            $temp = [];

            foreach ($data as $key => $value) {
                $temp[$key] = htmlspecialchars(trim($value));
            }

            return $temp;
        }

        return htmlspecialchars(trim($data));
    }
}
