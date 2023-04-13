<?php

// enable coroutine support
use Swoole\Coroutine\System;

try {
    Swoole\Runtime::enableCoroutine(true);
} catch (ErrorException $e) {
}

// create a Swoole HTTP server
$http = new Swoole\Http\Server("127.0.0.1", 9501);

// define some routes
$routes = [
    '/' => static function ($request, $response) {
        $response->header('Content-Type', 'text/plain');
        $response->end('Hello, World!');
    },
    '/users' => static function ($request, $response) {
        $timestamp = microtime(true);

        go(static function () use ($timestamp, $response) {
            error_log("Request: $timestamp  started\n");

            $email = 'test' . $timestamp . '@example.com';
            $name = 'test' . $timestamp;
            // delay for 1 second to simulate a slow I/O operation
            System::sleep(10);

            try {
                // create a PDO connection
                $dsn = "mysql:host=localhost;dbname=test";
                $username = "long";
                $password = "tnt";
                $pdo = new PDO($dsn, $username, $password);

                // prepare and execute a SQL statement
                $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                $stmt->execute([$name, $email]);

                $response->header('Content-Type', 'application/json');
                error_log("Request: $timestamp successfully\n");
                $response->end(json_encode(['message' => 'User created successfully']));
            } catch (PDOException $e) {
                $response->status(500);
                $response->header('Content-Type', 'text/plain');
                $response->end('Error creating user');
            }
        });
    },
];

// define some middleware
$middleware = [
    static function ($request, $response) {
        // do some authentication or other processing here
    },
];

// handle HTTP requests asynchronously with routing and middleware
$http->on('request', function ($request, $response) use ($routes, $middleware) {
    // apply middleware
    foreach ($middleware as $m) {
        $m($request, $response);
    }

    // find the matching route
    $route = $routes[$request->server['request_uri']] ?? null;

    // if the route exists, handle the request
    if ($route) {
        $route($request, $response);
    } else {
        $response->status(404);
        $response->header('Content-Type', 'text/plain');
        $response->end('Not Found');
    }
});

// start the HTTP server
$http->start();

