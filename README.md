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

That's all there is to it!
