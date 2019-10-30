<?php declare(strict_types=1);

namespace AngelTrs\PhpFramework;

use Whoops\Handler\PrettyPageHandler;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Narrowspark\HttpEmitter\SapiEmitter;

session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';

$settings = require dirname(__DIR__) . '/config/production/config.php';
$environment = $settings['environment'];

error_reporting(E_ALL);

$injector = include 'Dependencies.php';

$whoops = new \Whoops\Run;
$logger = $injector->make('Monolog\Logger');

if ($environment !== 'production') {
    $whoops->prependHandler(new PrettyPageHandler);
} else {
    $whoops->prependHandler(function (\Exception $e, $inspector, $run) use($logger) {
        $logger->error($e->getMessage());
    });
}

$whoops->register();

$request = $injector->make('Zend\Diactoros\Request');
$response = $injector->make('Zend\Diactoros\Response');

$routeDefinitionCallback = function (RouteCollector $r) {
    $routes = include('Routes.php');
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
};

$dispatcher = simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        $response->withHeader('Content-Type', 'text/html');
        $response->withStatus(404);
        $response->getBody()->write('404 - page not found');
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response->withHeader('Content-Type', 'text/html');
        $response->withStatus(405);
        $response->getBody()->write('405 - method not allowed');
        break;
    case \FastRoute\Dispatcher::FOUND:
        $className = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];
        $class = $injector->make($className);
        $response = $class->$method($vars);
        break;
}

$emitter = new SapiEmitter();
$emitter->emit($response);