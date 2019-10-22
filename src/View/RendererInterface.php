<?php declare(strict_types=1);

namespace AngelTrs\PhpFramework\View;

interface RendererInterface
{
    public function render($template, $data = []) : string;
}