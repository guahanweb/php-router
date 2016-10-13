<?php
namespace GuahanWeb\Http;

class Response {
    protected $headers;

    /**
     * Initialize a new response object, defaulting to HTML type
     *
     * @constructor
     */
    public function __construct() {
        $this->headers = array(
            'content-type' => 'text/html'
        );
    }

    /**
     * Set a header to be sent with the response
     *
     * @param {string} The header name
     * @param {string} The header value
     * @return void
     */
    public function setHeader($k, $v) {
        $this->headers[$k] = $v;
    }

    /**
     * Set multiple headers to be sent with the response
     *
     * @param {array} A list of headers to be applied
     * @return void
     */
    public function setHeaders($headers) {
        foreach ($headers as $k => $v) {
            $this->setHeader($k, $v);
        }
    }

    /**
     * Send the response to the consumer
     *
     * @param {*} $body The content to send. If the content is an array or object, it will be treated as a JSON payload
     * @return void
     */
    public function send($body, $code = 200) {
        // Handle JSON payloads
        if (is_array($body) || is_object($body)) {
            $this->setHeader('content-type', 'application/json');
            $body = json_encode($body);
        }

        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }

        http_response_code($code);
        echo $body;
        exit;
    }
}

