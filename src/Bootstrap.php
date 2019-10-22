<?php declare(strict_types=1);

namespace AngelTrs\PhpFramework;

use FastRoute\RouteCollector;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Narrowspark\HttpEmitter\SapiEmitter;
use Whoops\Handler\PrettyPageHandler;
use function FastRoute\simpleDispatcher;

require_once dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

$environment = 'production';

$whoops = new \Whoops\Run;
$logger = new Logger('errors');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/exception.log'));

if ($environment !== 'production') {
    $whoops->prependHandler(new PrettyPageHandler);
} else {
    $whoops->prependHandler(function ($e, $inspector, $run) use($logger) {
        $logger->error($e->getMessage());
    });
}
$whoops->register();

$injector = include('Dependencies.php');

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
        $class = $injector->make();
        $class->$method($vars);
        break;
}

$emitter = new SapiEmitter();
$emitter->emit($response);