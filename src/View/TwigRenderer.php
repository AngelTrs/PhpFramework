<?php declare(strict_types=1);

namespace AngelTrs\PhpFramework\View;

use Twig\Environment;

class TwigRenderer implements RendererInterface
{
    private $renderer;

    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render($template, $data = []) : string {
        return $this->renderer->render("$template.html", $data);
    }
}