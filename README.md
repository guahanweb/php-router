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

You may also choose to greedily match blocks of the path for manual parsing.
For instance, if we wanted to distinguish between a `json` extension to a
URI agnostic of the inner match, we can do something like this:

```php
// match a path with json extension
// URI: /api/v1/my_method/json
$router->get('/api/{path*}/json', function ($req, $res) {
    $res->send($req->path); // v1/my_method
});
```

This type of matching may be useful for doing a passthru to request static
files not managed by Apache or Nginx configs:

```php
// route all image requests to a handler
$router->get('/img/{image*}', function ($req, $res) {
    ImageManager::serve($req->image);
});
```
### Request and response objects

Each route handler will be passed two parameters: a request and a response
object. These objects are able to be used to get detailed information and
calculate the appropriate response. In most cases, a route handler should
end with a call to `$res->send()`.

## Request Object

When a router is initialized, a new `Http\Request` object is created to be
passed into the handler. This object contains a lot of pre-parsed attributes
to help manage your response.

### HTTP request type

The HTTP method (or verb) will be assigned to the request object and can be
accessed by property:

```php
echo $req->method;
```

Valid supported types are `GET`, `POST`, `PUT` and `DELETE`.

### HTTP request headers

All request headers are accessible on the request object as well. Rather than
using the native PHP `getallheaders()` method, we are using a custom polyfill
to allow retrieval of the headers in both Apache and Nginx environments.

Some request headers will be used by the router to set default values on the
response object. To access the headers, you may reference the `headers` property
of the request object:

```php
$router->post('/api/user', function ($req, $res) {
    if ($req->headers['Content-Type'] == 'application/json') {
        // request has JSON payload
    }
});
```

### Query string

The request object will pre-parse the query string and assign it to the `query`
property. If you are doing logic based on expected query parameters, use this
property.

```php
$router->post('/calendar', function ($req, $res) {
    if (!isset($req->query['month'])) {
        $res->send('Please select a month!');
    } else {
        $res->send(Calendar::render($req->query['month']));
    }
});
```

### Request URI

The fully matched URI is assigned to the `uri` property of the request object.

```php
$router->get('/profile/{username}', function ($req, $res) {
    $res->send($req->uri);
});
```

In this example, if you then navigate to http://yourdomain.com/profile/guahanweb,
you would get a response of `/profile/guahanweb`.

### Request body (data payload)

Parsing the request body is somethig we have tried to optimize a little. Rather
than always parsing the payload, we will statically parse it *only when it has
been requested the first time*. In other words, the body will not be parsed until
the first time the `body` attribute is referenced. If you reference the `body`
property again, the payload will not be parsed a second time.

Additionally, there are a few assumptions that are made about the body based on
the combination of HTTP verb and Content-Type header.

#### GET and DELETE

According to the HTTP/1.1 spec, `GET` and `DELETE` request bodies, if present,
should not have any meaning. Therefore, the request object will short circuit
any body parsing with a `null` value on these verbs.

#### PUT

The body for a `PUT` request will be read from `STDIN`. If the Content-Type of
the request is `application/json`, the body will also be parsed for JSON content,
and the resulting object will be returned.

#### POST

The `POST` verb acts just like `PUT` with one additional caveat. In addition to
a JSON payload, the `POST` verb can also take a Content-Type beginning with
`mulipart/form-data`. In this case, since PHP has already processed the payload
into the `$_POST` variable, the body will simply return that variable.

In both `PUT` and `POST` cases, if neither JSON nor form content are specified,
the raw string of the body will be returned.

## Response Object
