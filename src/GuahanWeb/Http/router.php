<?php
namespace GuahanWeb\Http;

class Router {
    protected $routes;

    protected function __construct() {
        $this->routes = array(
            'GET' => array(),
            'POST' => array(),
            'PUT' => array(),
            'DELETE' => array()
        );
    }

    static public function instance() {
        static $instance;

        if (is_null($instance)) {
            $instance = new Router();
        }

        return $instance;
    }

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

    public function get($route, $handler) {
        $this->routes['GET'][$route] = $handler;
    }

    public function process() {
        $request = new Request();
        $response = new Response();

        if (false === ($handler = $this->match($request->method, $request->uri, $params))) {
            // Error case
            $response->send('Not found', 404);
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
