<?php

use Swoole\Http\Request;
use Swoole\Http\Response;

use function Swoole\Coroutine\go;

try {
    Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

    Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
} catch (ErrorException $e) {
    error_log('error when setting hooks and enabling coroutine');
    exit;
}

$http = new Swoole\Http\Server("127.0.0.1", 9502);

$http->on('start', static function () {
    echo "Starting listen on port 9502";
});

$http->on('request', static function (Request $request, Response $response) {
    if ($request->server['request_uri'] === '/users') {
        go(function () use ($response) {
            $timestamp = microtime(true);
            error_log("Request $timestamp started");
            $email = 'test' . $timestamp . '@example.com';
            $name = 'test' . $timestamp;
            $dsn = "mysql:host=localhost;dbname=test";
            $username = "long";
            $password = "tnt";
            $pdo = new PDO($dsn, $username, $password);
            $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
            error_log("Request $timestamp successfully");
            $response->header('Content-Type', 'application/json');
            $response->end(
                json_encode([
                    'status' => 'success',
                    'message' => 'create user successfully',
                ], JSON_THROW_ON_ERROR)
            );
        });
    } else {
        $response->header('Content-Type', 'application/json');
        $response->end(
            json_encode([
                'status' => 'error',
                'message' => 'not found',
            ], JSON_THROW_ON_ERROR)
        );
    }
});

$http->start();
