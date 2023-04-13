<?php

use Swoole\Http\Request;
use Swoole\Http\Response;

class Router
{
    private $routes = [];

    public function addRoute($method, $path, $handler)
    {
        $this->routes[$method][$path] = $handler;
    }

    public function matchRoute($method, $path)
    {
        return $this->routes[$method][$path];
    }
}

class Middleware
{
    private $handlers = [];

    public function addHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function handleRequest($request, $response)
    {
        foreach ($this->handlers as $handler) {
            $handler($request, $response);
        }
    }
}

class App
{
    private $router;
    private $middleware;
    private $settings = [];
    private $cache = [];
    private $view;

    public function __construct()
    {
        $this->router = new Router();
        $this->middleware = new Middleware();
    }

    public function use($handler)
    {
        $this->middleware->addHandler($handler);
    }

    public function get($path, $handler)
    {
        $this->router->addRoute('GET', $path, $handler);
    }

    public function run()
    {
        $server = new Swoole\Http\Server("127.0.0.1", 8080);

        $server->on("request", function ($request, $response) {
            $route = $this->router->matchRoute($request->server['request_method'], $request->server['path_info']);
            $this->middleware->handleRequest($request, $response);
            $route($request, $response);
        });

        $server->start();
    }
}

$app = new App();

$app->get('/', function (Request $request, Response $response) {
    $response->setHeader('Content-Type', 'application/json');
    $response->end("Hello, world!");
});

$app->get('/hello', function ($request, $response) {
    $response->setHeader('Content-Type', 'application/json');
    $response->end(json_encode(['status' => 'success', 'message' => 'Hello, world!']));
});

$app->run();
