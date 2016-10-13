<?php
namespace GuahanWeb\Http;

class Request {
    protected $method;
    protected $uri;
    protected $query;
    protected $headers;

    protected $params;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        if (stripos($this->method, 'HEAD')) {
            // HEAD requests must immediately return with no body
            exit;
        }

        $this->uri = $_SERVER['REQUEST_URI'];
        $this->query = empty($_SERVER['QUERY_STRING']) ? array() : parse_str($_SERVER['QUERY_STRING']);
        $this->headers = $this->getAllHeaders();

        $this->params = array();
    }

    public function __get($k) {
        switch ($k) {
            case 'method':
            case 'headers':
            case 'query':
            case 'uri':
                return $this->$k;
                break;

            default:
                return isset($this->params[$k]) ? $this->params[$k] : null;
        }
    }

    // we only can write the params directly
    public function __set($k, $v) {
        $this->params[$k] = $v;
    }

    protected function getAllHeaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

