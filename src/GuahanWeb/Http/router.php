<?php
namespace GuahanWeb\Http;

class RouterException extends \Exception {}

class Router {
    protected $routes;
    protected $supported_methods;
    protected $default_handlers;

    protected function __construct() {
        $this->supported_methods = array('GET', 'POST', 'PUT', 'DELETE');
        $this->routes = array();
        foreach ($this->supported_methods as $method) {
            $this->routes[$method] = array();
            $this->default_handlers[$method] = function ($req, $res) {
                // All verbs will return a 404 by default
                $res->send('Not found', 404);
            };
        }
    }

    /**
     * Retrieves the shared router instance
     *
     * @public
     * @return {Router}
     */
    static public function instance() {
        static $instance;

        if (is_null($instance)) {
            $instance = new Router();
        }

        return $instance;
    }

    /**
     * Looks for a matching registered route for the provided method and uri combination.
     *
     * @protected
     * @param {string} $method The HTTP verb of the request
     * @param {string} $uri The URI of the request
     * @param {array} $params Optional reference for extracted parameters
     * @return {function|false}
     */
    protected function match($method, $uri, &$params = null) {
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                if ($uri == $route) {
                    // exact match
                    return $handler;
                } elseif (false !== preg_match_all('/\{([^}]+)\}/', $route, $match, PREG_PATTERN_ORDER)) {
                    $args = $match[1];
                    if (count($args) > 0) {
                        $keys = array();
                        $replacements = array();

                        foreach ($args as $k => $arg) {
                            $keys[] = rtrim($arg, '*');
                            $replacements[] = (substr($arg, -1) == '*') ? '(.+?)' : '([^/]+)';
                        }

                        $matcher = '/^' . str_replace('/', '\/', str_replace($match[0], $replacements, $route)) . '$/';
                        if (preg_match($matcher, $uri, $placeholders)) {
                            // update params and return
                            array_shift($placeholders);
                            $params = array();
                            foreach ($keys as $k => $arg) {
                                $params[$arg] = $placeholders[$k];
                            }

                            return $handler;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Registers a new route
     *
     * @param {string|array} $method The HTTP method(s) this route supports
     * @param {string} $route The route pattern to register
     * @param {function} $handler The handler to execute when route and method matches
     * @return void
     */
    public function route($method, $route, $handler) {
        if (is_array($method)) {
            foreach ($method as $m) {
                $this->route($m, $route, $handler);
            }
        } elseif ($method == '*') {
            foreach ($this->supported_methods as $m) {
                $this->route($m, $route, $handler);
            }
        } elseif (in_array(strtoupper($method), $this->supported_methods)) {
            $this->routes[strtoupper($method)][$route] = $handler;
        } else {
            throw new RouterException(sprintf('Unsupported HTTP verb provided for route: %s', $method));
        }
    }

    /**
     * Shorthand to register GET handlers
     *
     * @param {string} $route The route pattern to register
     * @param {function} $handler The handler to execute when route matches
     * @return void
     */
    public function get($route, $handler) {
        $this->route('GET', $route, $handler);
    }

    /**
     * Shorthand to register POST handlers
     *
     * @param {string} $route The route pattern to register
     * @param {function} $handler The handler to execute when route matches
     * @return void
     */
    public function post($route, $handler) {
        $this->route('POST', $route, $handler);
    }

    /**
     * Shorthand to register PUT handlers
     *
     * @param {string} $route The route pattern to register
     * @param {function} $handler The handler to execute when route matches
     * @return void
     */
    public function put($route, $handler) {
        $this->route('PUT', $route, $handler);
    }

    /**
     * Shorthand to register DELETE handlers
     *
     * @param {string} $route The route pattern to register
     * @param {function} $handler The handler to execute when route matches
     * @return void
     */
    public function delete($route, $handler) {
        $this->route('DELETE', $route, $handler);
    }

    /**
     * Activates the router to begin handling the HTTP request. If no matching routes are found,
     * we will automatically send a 404.
     *
     * @return void
     */
    public function process() {
        $request = new Request();
        $response = new Response();

        if (false === ($handler = $this->match($request->method, $request->uri, $params))) {
            // Error case
            $this->default_handlers[$request->method]($request, $response);
        }

        // route params applied to the request object
        if (null !== $params) {
            foreach ($params as $k => $v) {
                $request->$k = $v;
            }
        }

        $handler($request, $response);
    }
}
