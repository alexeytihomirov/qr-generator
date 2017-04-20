<?php
namespace TrekkSoft\QrCodeGenerator\Renderer;

use TrekkSoft\QrCodeGenerator\Image;
use TrekkSoft\QrCodeGenerator\Exception\RenderException;

interface RendererInterface
{
    /**
     * @param string $text
     * @param int $width
     * @param int $height
     * @return Image
     * @throws RenderException
     */
    public function render($text, $width, $height);
}