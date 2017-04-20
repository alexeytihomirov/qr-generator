<?php

require 'vendor/autoload.php';

use \TrekkSoft\QrCodeGenerator\QrCode;
use \TrekkSoft\QrCodeGenerator\Renderer\GoogleChartsRenderer;

$qrCode = new QrCode('TrekkSoft', 50, 50);

$qrCode->setRenderer((new GoogleChartsRenderer())->setMargin(0)->setErrorCorrectionLevel('L'));
$qrCodeData = $qrCode->generate();

echo $qrCodeData;

imagepng($qrCodeData->createImage(), 'img.png');