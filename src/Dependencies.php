<?php declare(strict_types=1);

$injector = new \Auryn\Injector;

$injector->alias('Psr\Http\Message\RequestInterface', 'Zend\Diactoros\Request');
$injector->delegate('Zend\Diactoros\Request',
    'Zend\Diactoros\ServerRequestFactory::fromGlobals');
$injector->share('Zend\Diactoros\Request');

$injector->alias('Psr\Http\Message\ResponseInterface', 'Zend\Diactoros\Response');
$injector->share('\Zend\Diactoros\Response');

$injector->alias('AngelTrs\PhpFramework\View\RendererInterface', 'AngelTrs\PhpFramework\View\TwigRenderer');

$injector->delegate('Twig\Environment', function () use ($injector) {
    $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/resources/views');
    $twig = new \Twig\Environment($loader);
    return $twig;
});

return $injector;