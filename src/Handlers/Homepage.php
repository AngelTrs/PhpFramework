<?php declare(strict_types=1);

namespace AngelTrs\Phpframework\Handlers;

use AngelTrs\PhpFramework\View\RendererInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Homepage
{
    private $request;
    private $response;
    private $renderer;

    public function __construct(RequestInterface $request, ResponseInterface $response, RendererInterface $renderer)
    {
        $this->request = $request;
        $this->response = $response;
        $this->response->withHeader('Content-Type', 'text/html');
        $this->renderer = $renderer;
    }

    public function show($params)
    {
        $name = (!empty($params) ? $params["name"] : 'stranger');

        $data = [
            'name' => $name,
        ];
        $htmlContent = $this->renderer->render('Homepage', $data);
        $this->response->getBody()->write($htmlContent);
    }
}