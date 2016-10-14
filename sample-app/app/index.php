<?php
require './vendor/autoload.php';

use GuahanWeb\Http;

$router = Http\Router::instance();

// example error response
$router->get('/error', function ($req, $res) {
    $res->send('Error page', 500);
});

// examine some request properties
$router->route('*', '/info', function ($req, $res) {
    $res->send(array(
        'method' => $req->method,
        'uri' => $req->uri,
        'query' => $req->query,
        'headers' => $req->headers,
        'body' => $req->body
    ));
});

// requests to sample-app.com/
$router->get('/', function ($req, $res) {
    $res->send('Hello, world!');
});

// start the app
$router->process();
