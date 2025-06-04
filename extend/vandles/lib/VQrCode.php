<?php
/*
 * 
 * Author: vandles
 * Date: 2022/11/28 18:04
 * Email: <vandles@qq.com>
 */


namespace vandles\lib;


use Endroid\QrCode\QrCode;

class VQrCode {

    /** @var QrCode */
    private $qrCode;
    private $logo;
    private $logoWidth;

    public static function instance():VQrCode {
        return app()->make(VQrCode::class);
    }

    public function setLogo($logo, $width=60):VQrCode {
        $this->logo = $logo;
        $this->logoWidth = $width;
        return $this;
    }

    /**
     * 生成二维码uri
     * @param $txt
     * @param int $width
     * @param $logo
     * @param int $logoWidth
     * @return string
     * @throws \Endroid\QrCode\Exceptions\DataDoesntExistsException
     */
    public function genQrCodeUri($txt, $width=300) {
        $this->qrCode = $this->createQrCode($txt, $width);
        $this->addOptions();

        $dataUri = $this->qrCode->getDataUri();
        return $dataUri;
    }

    /**
     * 生成二维码
     * @param $txt
     * @param int $width
     * @param null $logo
     * @param int $logoWidth
     * @return string
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionFailedException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     * @throws \Endroid\QrCode\Exceptions\DataDoesntExistsException
     */
    public function genQrCodePng($txt, $width=300) {
        $qrCode = $this->createQrCode($txt, $width);
        $this->addOptions();

        $png = $qrCode->get('png');
        return $png;
    }

    /**
     * 创建二维码对象
     * @param $txt
     * @param int $width
     * @return QrCode
     */
    private function createQrCode($txt, $width=300):QrCode {
        $qrCode = new QrCode();
        $qrCode->setText($txt);
        $qrCode->setSize($width);

        return $qrCode;
    }

    /**
     * 添加二维码属性
     * @throws \Endroid\QrCode\Exceptions\DataDoesntExistsException
     */
    private function addOptions(){
        if($this->logo) $this->qrCode->setLogo($this->logo)->setLogoSize($this->logoWidth);

    }

}