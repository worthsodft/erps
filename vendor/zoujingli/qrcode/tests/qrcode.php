<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

include "../vendor/autoload.php";

$result = Builder::create()
    ->writer(new PngWriter())
    ->writerOptions([])
    ->data('Custom QR code contents')
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
    ->size(300)
    ->margin(15)
    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
    ->logoPath(dirname(__DIR__) . '/assets/icon.png')
    ->logoResizeToWidth(65)
    ->logoResizeToHeight(65)
    ->labelText('This is the label')
//    ->labelFont(new OpenSans(20))
    ->labelAlignment(new LabelAlignmentCenter())
    ->validateResult(false)
    ->build();

// 输出显示二维码
header("content-type:{$result->getMimeType()}");
echo $result->getString();