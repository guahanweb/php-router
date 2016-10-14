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

    /**
     * Let's try to make some variables more accessible for consumption
     *
     * @public
     * @param {string} $k The key being requested
     * @return {mixed}
     */
    public function __get($k) {
        static $body;

        switch ($k) {
            case 'method':
            case 'headers':
            case 'query':
            case 'uri':
                return $this->$k;
                break;

            case 'body':
                // We will only parse the body if it is requested, and then only
                // once. We want to keep the processing as light as possible
                if (is_null($body)) {
                    $body = $this->parseBody();
                }
                return $body;
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

    /**
     * Attempt to parse the body of the request (considering all HTTP verbs and limitations).
     *
     * @protected
     * @return {string|object|null}
     */
    protected function parseBody() {
        // no body allowed for GET or DELETE requests
        if ($this->method == 'GET' || $this->method == 'DELETE') {
            return null;
        }

        $data = null;
        if ($this->method == 'POST' && isset($this->headers['Content-Type']) && strpos($this->headers['Content-Type'], 'multipart/form-data') === 0) {
            // POST requests may have multipart/form-data already parsed
            $data = $_POST;
        } else {
            // handle raw body, and parse if content-type if application/json
            $data = file_get_contents('php://input');
            if (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] == 'application/json') {
                $data = json_decode(trim($data));
            }
        }
        return $data;
    }
}

