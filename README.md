# PHP Router

PHP Router is a lightweight library to help with managing HTTP requests,
responses and routing in order to build robust applications quickly. This
library uses patterns most commonly found in popular Node.js libraries
like Express and Hapi.

In order to glean the most benefit from implementing this pattern of
routing, you may wish to make some adjustments to your Apache or Nginx
configuration.

## Installation

Install with [Composer](https://www.getcomposer.org):
```
$ composer install guahanweb/php-router
```

## Usage

Once you have installed and configured your project to use Composer's
autoload, you can begin routing.

```php
<?php
// Include the namespace
use GuahanWeb\Http;

// Get a router instance
$router = Http\Router::instance();

// Register a route
$router->get('/', function ($req, $res) {
    $res->send('Hello, world!');
});

// Tell the router to start
$router->process();
```

## Router

Routing is supported in the same basic pattern as Express.js. Provided
an HTTP verb, route pattern and handler, the handler will be executed
whenever the pattern and verb combination match the registered route.

### Registering a route

A route may be registered one of two ways. The first is by explicit verb
methods:

```php
$router->get($route, $handler);
$router->post($route, $handler);
$router->put($route, $handler);
$router->delete($route, $handler);
```

A second option is to register one or more verbs (or all with a `*`) in
a single registration call:

```php
// multiple verbs
$router->route(['POST', 'PUT'], $route, $handler);

// wildcard for all supported verbs
$router->route('*', $route, $handler);
```

### Route patterns and parameters

If you are wishing to capture parameters from your route URI, you may use
brackets to name your parameters. These parameters will be applied as
properties on the request object:

```php
// capture a username
$router->get('/profile/{username}', function ($req, $res) {
    var_dump($req->username);
});
```

You may also choose to greedily grab blocks of the path for manual parsing.
For instance, if we wanted to distinguish between a `json` extension to a
URI agnostic of the inner match, we can do something like this:

```php
// match a path with json extension
// URI: /api/v1/my_method/json
$router->get('/api/{path*}/json', function ($req, $res) {
    // $req->path == 'v1/my_method'
});
```

