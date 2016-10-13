<?php
namespace GuahanWeb\Http;

class Response {
    protected $headers;

    public function __construct() {
        $this->headers = array();
    }

    public function setHeader($k, $v) {
        $this->headers[$k] = $v;
    }

    public function setHeaders($headers) {
        foreach ($headers as $k => $v) {
            $this->setHeader($k, $v);
        }
    }

    public function send($body, $code = 200) {
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }

        http_response_code($code);
        echo $body;
        exit;
    }
}

