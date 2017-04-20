<?php
namespace TrekkSoft\QrCodeGenerator;

use TrekkSoft\QrCodeGenerator\Renderer\GoogleChartsRenderer;
use TrekkSoft\QrCodeGenerator\Renderer\RendererInterface;

class QrCode
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var int in pixels
     */
    private $width;

    /**
     * @var int in pixels
     */
    private $height;

    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct($text, $width, $height = null)
    {
        $this->setText($text);
        $this->setWidth($width);
        $this->setHeight((null === $height) ? $this->getWidth() : $height);
    }

    /**
     * @return Image
     */
    public function generate()
    {
        return $this->getRenderer()->render($this->text, $this->width, $this->height);
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return RendererInterface
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->setRenderer(new GoogleChartsRenderer());
        }

        return $this->renderer;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return QrCode
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return QrCode
     */
    public function setWidth($width)
    {
        if (!is_int($width) || $width <= 0) {
            throw new \InvalidArgumentException('Width must be positive integer number');
        }

        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return QrCode
     */
    public function setHeight($height)
    {
        if (!is_int($height) || $height <= 0) {
            throw new \InvalidArgumentException('Height must be positive integer number');
        }

        $this->height = $height;

        return $this;
    }
}