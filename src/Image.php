<?php
namespace TrekkSoft\QrCodeGenerator;

class Image
{
    /**
     * @var string
     */
    private $rawImageData;

    public function __construct($rawImageData)
    {
        $this->rawImageData = $rawImageData;
    }

    /**
     * @return array
     */
    public function getImageData()
    {
        return getimagesizefromstring($this->rawImageData);
    }

    /**
     * @return resource
     */
    public function createImage()
    {
        return imagecreatefromstring($this->rawImageData);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->rawImageData;
    }
}