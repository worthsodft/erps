# 二维码工具 QrCode

[![Latest Stable Version](http://img.shields.io/packagist/v/zoujingli/qrcode.svg)](https://packagist.org/packages/zoujingli/qrcode)
[![Latest Unstable Version](https://poser.pugx.org/zoujingli/qrcode/v/unstable)](https://packagist.org/packages/zoujingli/qrcode)
[![Total Downloads](http://img.shields.io/packagist/dt/zoujingli/qrcode.svg)](https://packagist.org/packages/zoujingli/qrcode)
[![Monthly Downloads](http://img.shields.io/packagist/dm/zoujingli/qrcode.svg)](https://packagist.org/packages/zoujingli/qrcode)
[![License](http://img.shields.io/packagist/l/zoujingli/qrcode.svg)](https://packagist.org/packages/zoujingli/qrcode)

二维码工具`zoujingli/qrcode`是`fork`自`endroid/qr-code`进行修改来，最低支持`php7.1`版本；

详细文档见原仓库：https://github.com/endroid/qr-code

> **为满足在`PHP7`运行需求，对原仓库进行如下修改：**
> 1. 修改代码语法最低可在`PHP7.1`上运行；
> 2. 原仓库的开源协议不变，未增加额外功能代码；
> 3. 目前已测试`PHP`环境有`PHP7.1` `PHP7.2` `PHP7.4`；
> 4. 去除原仓库的部分测试代码及字体文件，优化安装包体积；

```shell
// 安装执行指令
composer require zoujingli/qrcode
```

常规配置如下，更多参数使用请阅读其官方文档。

```php
$result = \Endroid\QrCode\Builder\Builder::create()
    ->writer(new \Endroid\QrCode\Writer\PngWriter())
    ->writerOptions([])
    ->data('Custom QR code contents')
    ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
    // ->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
    ->size(300)
    ->margin(10)
    // ->roundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
    
    //// 设置二维码 LOGO 图片
    // ->logoPath(__DIR__.'/icon.png')
    
    //// 设置二维码标签字段
    // ->labelText('This is the label')
    // ->labelFont(new \Endroid\QrCode\Label\Font\OpenSans(20))
    // ->labelAlignment(new \Endroid\QrCode\Label\Alignment\LabelAlignmentCenter())
    ->validateResult(false)
    ->build();

// 输出 Base64 图片
echo $result->getDataUri();

```

#### Usage: using the builder

```php
$result = \Endroid\QrCode\Builder\Builder::create()
    ->writer(new \Endroid\QrCode\Writer\PngWriter())
    ->writerOptions([])
    ->data('Custom QR code contents')
    ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
    ->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
    ->size(300)
    ->margin(10)
    ->roundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
    ->logoPath(__DIR__.'/icon.png')
    ->labelText('This is the label')
     ->labelFont(new \Endroid\QrCode\Label\Font\OpenSans(20))
    ->labelAlignment(new \Endroid\QrCode\Label\Alignment\LabelAlignmentCenter())
    ->validateResult(false)
    ->build();
```

#### Usage: without using the builder

```php
$writer = new \Endroid\QrCode\Writer\PngWriter();

// Create QR code
$qrCode = \Endroid\QrCode\QrCode::create('Life is too short to be generating QR codes')
    ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
    ->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow())
    ->setSize(300)
    ->setMargin(10)
    ->setRoundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin())
    ->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0))
    ->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

// Create generic logo
$logo = \Endroid\QrCode\Logo\Logo::create(__DIR__.'/icon.png')->setResizeToWidth(50);

// Create generic label
$label = \Endroid\QrCode\Label\Label::create('Label')
->setTextColor(new \Endroid\QrCode\Color\Color(255, 0, 0));

$result = $writer->write($qrCode, $logo, $label);

// Validate the result
$writer->validateResult($result, 'Life is too short to be generating QR codes');
```

#### Usage: working with results

```php

// Directly output the QR code
header('Content-Type: '.$result->getMimeType());
echo $result->getString();

// Save it to a file
$result->saveToFile(__DIR__.'/qrcode.png');

// Generate a data URI to include image data inline (i.e. inside an <img> tag)
$dataUri = $result->getDataUri();
```

##### Writer options

```php
use Endroid\QrCode\Writer\SvgWriter;

$builder->setWriterOptions([SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true]);
```

##### Encoding

If you use a barcode scanner you can have some troubles while reading the
generated QR codes. Depending on the encoding you chose you will have an extra
amount of data corresponding to the ECI block. Some barcode scanner are not
programmed to interpret this block of information. To ensure a maximum
compatibility you can use the `ISO-8859-1` encoding that is the default
encoding used by barcode scanners (if your character set supports it,
i.e. no Chinese characters are present).

##### Round block size mode

By default block sizes are rounded to guarantee sharp images and improve
readability. However some other rounding variants are available.

* `margin (default)`: the size of the QR code is shrunk if necessary but the size
  of the final image remains unchanged due to additional margin being added.
* `enlarge`: the size of the QR code and the final image are enlarged when
  rounding differences occur.
* `shrink`: the size of the QR code and the final image are
  shrunk when rounding differences occur.
* `none`: No rounding. This mode can be used when blocks don't need to be rounded
  to pixels (for instance SVG).

#### Readability

The readability of a QR code is primarily determined by the size, the input
length, the error correction level and any possible logo over the image, so you
can tweak these parameters if you are looking for optimal results. You can also
check $qrCode->getRoundBlockSize() value to see if block dimensions are rounded
so that the image is more sharp and readable. Please note that rounding block
size can result in additional padding to compensate for the rounding difference.
And finally the encoding (default UTF-8 to support large character sets) can be
set to `ISO-8859-1` if possible to improve readability.

#### Validating the generated QR code

If you need to be extra sure the QR code you generated is readable and contains
the exact data you requested you can enable the validation reader, which is
disabled by default. You can do this either via the builder or directly on any
writer that supports validation. See the examples above.

Please note that validation affects performance so only use it in case of problems.

#### Symfony integration

The [endroid/qr-code-bundle](https://github.com/endroid/qr-code-bundle)
integrates the QR code library in Symfony for an even better experience.

* Configure your defaults (like image size, default writer etc.)
* Support for multiple configurations and injection via aliases
* Generate QR codes for defined configurations via URL like /qr-code/<config>/Hello
* Generate QR codes or URLs directly from Twig using dedicated functions

Read the [bundle documentation](https://github.com/endroid/qr-code-bundle)
for more information.

#### Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatibility
breaking changes will be kept to a minimum but be aware that these can occur.
Lock your dependencies for production and test your code when upgrading.

#### License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
