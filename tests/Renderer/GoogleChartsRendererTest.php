<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TrekkSoft\QrCodeGenerator\Image;
use PHPUnit\Framework\TestCase;
use TrekkSoft\QrCodeGenerator\Renderer\GoogleChartsRenderer;
use TrekkSoft\QrCodeGenerator\Exception\ValidationException;

class GoogleChartsRendererTest extends TestCase
{
    public function testCorrectErrorCorrectionLevel()
    {
        $renderer = (new GoogleChartsRenderer())
            ->setErrorCorrectionLevel(GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_L);

        $this->assertEquals(GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_L, $renderer->getErrorCorrectionLevel());
    }

    public function testIncorrectErrorCorrectionLevel()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new GoogleChartsRenderer())->setErrorCorrectionLevel('incorrectErrorCorrectionLevel');
    }

    public function testCorrectMargin()
    {
        $renderer = (new GoogleChartsRenderer())->setMargin(0);
        $this->assertEquals(0, $renderer->getMargin());

        $renderer = (new GoogleChartsRenderer())->setMargin(5);
        $this->assertEquals(5, $renderer->getMargin());
    }

    public function testIncorrectMargin()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new GoogleChartsRenderer())->setMargin(-1);
    }

    public function testDefaultHttpClient()
    {
        $this->assertInstanceOf(ClientInterface::class, (new GoogleChartsRenderer())->getHttpClient());
    }

    public function testHttpClientChange()
    {
        $renderer = new GoogleChartsRenderer();
        $httpClient = $this->getMockBuilder(ClientInterface::class)->getMock();
        $renderer->setHttpClient($httpClient);
        $this->assertSame($httpClient, $renderer->getHttpClient());
    }

    /**
     * @param string $text
     * @param string $errorCorrectionLevel
     * @dataProvider getValidText
     */
    public function testRenderTextValid($text, $errorCorrectionLevel)
    {
        $renderer = (new GoogleChartsRenderer())->setErrorCorrectionLevel($errorCorrectionLevel);

        $mockHandler = new MockHandler([new Response(200, [], 'test')]);
        $httpClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $renderer->setHttpClient($httpClient);
        $image = $renderer->render($text, 500, 500);
        $this->assertInstanceOf(Image::class, $image);
    }

    /**
     * @param string $text
     * @param string $errorCorrectionLevel
     * @dataProvider getValidText
     */
    public function testValidateTextInvalid($text, $errorCorrectionLevel)
    {
        $renderer = (new GoogleChartsRenderer())->setErrorCorrectionLevel($errorCorrectionLevel);
        $this->expectException(ValidationException::class);
        $renderer->render($text.'1', 500, 500);
    }

    public function getValidText()
    {
        return [
            [str_repeat(1, 7087), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_L],
            [str_repeat(1, 5594), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_M],
            [str_repeat(1, 3991), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_Q],
            [str_repeat(1, 3055), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_H],

            [str_repeat('W', 4295), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_L],
            [str_repeat('W', 3390), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_M],
            [str_repeat('W', 2418), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_Q],
            [str_repeat('W', 1851), GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_H],

            [str_repeat('ы', 1476).'1', GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_L],
            [str_repeat('ы', 1165).'1', GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_M],
            [str_repeat('ы', 831).'1', GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_Q],
            [str_repeat('ы', 636).'1', GoogleChartsRenderer::ERROR_CORRECTION_LEVEL_H],
        ];
    }
}