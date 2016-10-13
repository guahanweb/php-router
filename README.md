# PHP Router

This composer library allows simple, quick routing configuration.

```php
<?php
use GuahanWeb\Http;

$router = Http\Router::instance();

$router->get('/', function ($req, $res) {
    $res->send('Hello, world!');
});

$router->process();
```
