<?php
namespace TrekkSoft\QrCodeGenerator\Renderer;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use TrekkSoft\QrCodeGenerator\Exception\RenderException;
use TrekkSoft\QrCodeGenerator\Exception\ValidationException;
use TrekkSoft\QrCodeGenerator\Image;
use GuzzleHttp\Client;

class GoogleChartsRenderer implements RendererInterface
{
    const ENCODING_UTF8 = 'UTF-8';

    const ERROR_CORRECTION_LEVEL_L = 'L';
    const ERROR_CORRECTION_LEVEL_M = 'M';
    const ERROR_CORRECTION_LEVEL_Q = 'Q';
    const ERROR_CORRECTION_LEVEL_H = 'H';

    const API_URL = 'https://chart.googleapis.com/chart';

    /**
     * QR codes support four levels of error correction to enable recovery of missing, misread, or obscured data.
     * L - Allows recovery of up to 7% data loss
     * M - Allows recovery of up to 15% data loss
     * Q - Allows recovery of up to 25% data loss
     * H - Allows recovery of up to 30% data loss
     *
     * @var string
     */
    private $errorCorrectionLevel;

    /**
     * The width of the white border around the data portion of the code in rows
     *
     * @var int
     */
    private $margin;

    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(
        $errorCorrectionLevel = self::ERROR_CORRECTION_LEVEL_L,
        $margin = 4
    ) {
        $this->setErrorCorrectionLevel($errorCorrectionLevel);
        $this->setMargin($margin);
    }


    /**
     * {@inheritDoc}
     */
    public function render($text, $width, $height)
    {
        if (!$this->validateText($text)) {
            throw new ValidationException('Reached maximum number of bytes to encode');
        }

        try {
            $response = $this->getHttpClient()->request('post', self::API_URL, [
                'form_params' => [
                    'cht' => 'qr',
                    'chl' => $text,
                    'chs' => sprintf('%dx%d', $width, $height),
                    'choe' => self::ENCODING_UTF8,
                    'chld' => sprintf('%s|%d', $this->getErrorCorrectionLevel(), $this->getMargin()),
                ]
            ]);

            $content = $response->getBody()->getContents();

            return new Image($content);
        } catch (RequestException $e) {
            throw new RenderException('Failed to get data from google service', $e->getCode(), $e);
        }
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->setHttpClient(new Client());
        }

        return $this->httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     * @return GoogleChartsRenderer
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorCorrectionLevel()
    {
        return $this->errorCorrectionLevel;
    }

    /**
     * @param string $errorCorrectionLevel
     * @return GoogleChartsRenderer
     * @throws \InvalidArgumentException
     */
    public function setErrorCorrectionLevel($errorCorrectionLevel)
    {
        if (!in_array($errorCorrectionLevel, $this->getSupportedErrorCorrectionLevels())) {
            throw new \InvalidArgumentException(
                sprintf('Unsupported error correction level "%s"', $errorCorrectionLevel)
            );
        }

        $this->errorCorrectionLevel = $errorCorrectionLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * @param int $margin
     * @return GoogleChartsRenderer
     * @throws \InvalidArgumentException
     */
    public function setMargin($margin)
    {
        if (!is_int($margin) || $margin < 0) {
            throw new \InvalidArgumentException('Border margin must be positive integer number or 0');
        }

        $this->margin = $margin;

        return $this;
    }

    /**
     * @return array
     */
    public function getSupportedErrorCorrectionLevels()
    {
        return [
            self::ERROR_CORRECTION_LEVEL_L,
            self::ERROR_CORRECTION_LEVEL_M,
            self::ERROR_CORRECTION_LEVEL_Q,
            self::ERROR_CORRECTION_LEVEL_H,
        ];
    }

    private function validateText($text)
    {
        $textLength = strlen($text);
        $correctionLevel = $this->getErrorCorrectionLevel();

        return $textLength <= $this->getUtfValidationMatrix($text)[$correctionLevel];
    }

    private function getUtfValidationMatrix($text)
    {
        if (preg_match('/^[0-9]+$/', $text)) {

            return [
                self::ERROR_CORRECTION_LEVEL_L => 7087,
                self::ERROR_CORRECTION_LEVEL_M => 5594,
                self::ERROR_CORRECTION_LEVEL_Q => 3991,
                self::ERROR_CORRECTION_LEVEL_H => 3055,
            ];
        } elseif (preg_match('/^[0-9A-Z \$\*\%\+\.\/\:\-]+$/', $text)) {

            return [
                self::ERROR_CORRECTION_LEVEL_L => 4295,
                self::ERROR_CORRECTION_LEVEL_M => 3390,
                self::ERROR_CORRECTION_LEVEL_Q => 2418,
                self::ERROR_CORRECTION_LEVEL_H => 1851,
            ];
        }

        return [
            self::ERROR_CORRECTION_LEVEL_L => 2953,
            self::ERROR_CORRECTION_LEVEL_M => 2331,
            self::ERROR_CORRECTION_LEVEL_Q => 1663,
            self::ERROR_CORRECTION_LEVEL_H => 1273,
        ];
    }
}