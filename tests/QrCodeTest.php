<?php

use PHPUnit\Framework\TestCase;
use TrekkSoft\QrCodeGenerator\QrCode;
use \TrekkSoft\QrCodeGenerator\Renderer\GoogleChartsRenderer;

class QrCodeTest extends TestCase
{
    public function testDefaultGenerator()
    {
        $qrCode = new QrCode('TestMessage', 50, 50);
        $this->assertInstanceOf(GoogleChartsRenderer::class, $qrCode->getRenderer());
    }

    public function testRendererChange()
    {
        $qrCode = new QrCode('TestMessage', 50, 50);
        $renderer = $this->getMockBuilder(TrekkSoft\QrCodeGenerator\Renderer\RendererInterface::class)->getMock();
        $qrCode->setRenderer($renderer);
        $this->assertSame($renderer, $qrCode->getRenderer());
    }

    public function testGenerate()
    {
        $qrCode = new QrCode('TestMessage', 50, 50);
        $renderer = $this->getMockBuilder(TrekkSoft\QrCodeGenerator\Renderer\RendererInterface::class)->getMock();
        $renderer->expects($this->once())->method('render')->with('TestMessage', 50, 50);
        $qrCode->setRenderer($renderer);
        $qrCode->generate();
    }

    public function testValidWidthAndHeight()
    {
        $qrCode = new QrCode('TestMessage', 100, 150);
        $this->assertEquals(100, $qrCode->getWidth());
        $this->assertEquals(150, $qrCode->getHeight());
    }

    /**
     * @param int $width
     * @dataProvider getInvalidWidth
     */
    public function testInvalidWidth($width)
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrCode('TestMessage', $width);
    }

    /**
     * @param int $height
     * @dataProvider getInvalidHeight
     */
    public function testInvalidHeight($height)
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrCode('TestMessage', 100, $height);
    }

    public function getInvalidWidth()
    {
        return [
            [0],
            [-1],
        ];
    }

    public function getInvalidHeight()
    {
        return [
            [0],
            [-1],
        ];
    }
}