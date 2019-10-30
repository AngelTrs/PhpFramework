<?php declare(strict_types=1);

namespace AngelTrs\PhpFramework;

$injector = new \Auryn\Injector;

$injector->delegate('\Monolog\Logger', function () use ($injector) {
    $logger = new \Monolog\Logger('logger');
    $file_handler = new \Monolog\Handler\StreamHandler(dirname(__DIR__) .'/logs/'. $settings['projectName'] .'.log');
    $logger->pushHandler($file_handler);
    return $logger;
});

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
    $twig->addFunction(
        new \Twig\TwigFunction(
            'form_token',
            function () {
                if (empty($_SESSION['token'])) {
                    $_SESSION['token'] = bin2hex(random_bytes(32));
                }
                return $_SESSION['token'];
            }));
    return $twig;
});
$injector->share('Twig\Environment');

$injector->define('AngelTrs\PhpFramework\Handlers\Contact', [':settings' => $settings]);

$injector->delegate('Symfony\Component\Mailer\Mailer', function () use ($injector) {
    $transport = new \Symfony\Component\Mailer\Transport\Smtp\SmtpTransport();
    $mailer = new \Symfony\Component\Mailer\Mailer($transport);
    return $mailer;
});
$injector->share('\Symfony\Component\Mailer\Mailer');

return $injector;