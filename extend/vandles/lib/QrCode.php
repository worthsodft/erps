<?php
/**
 * Created by PhpStorm
 * User: vandles
 * Date: 2023/3/27
 * Time: 23:46
 * Email: windows_1122334@126.com
 */


namespace vandles\lib;


use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use think\facade\Request;

class QrCode {
    public static $instance;

    public static function instance() {
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }


    public function createQrCode($text, $isForce=false, $logofile=null, $topic='qrcode') {
        if(!$text) throw new \Exception('二维码内容不能为空');

        $filename = "qrcode_$text.png";
        $shortname = "/upload/_$topic";
        $publicname = app()->getRootPath() . 'public';
        $fullname = $publicname . $shortname . '/' . $filename;

        !is_dir($publicname . $shortname) && mkdir($publicname . $shortname, 0777, true);

        if(!file_exists($fullname) || $isForce){
            $result = $this->drawQrCode($text, $logofile);
            $result->saveToFile($fullname);
        }

        $url = Request::domain() . $shortname .  '/' . $filename;
        $shortname = $shortname . '/' . $filename;
        $data = compact('fullname','shortname', 'url');
        return $data;
    }

    private function drawQrCode($text, $logofile=null){
        $builder = Builder::create()
            ->writer(new PngWriter())
            ->data($text)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10);

        if($logofile) {
            $builder->logoPath($logofile)
                ->logoResizeToHeight(60)
                ->logoResizeToWidth(60);
        }
        return $builder->build();
    }

    private function url2shortname($url){
        return substr($url, strpos($url, '/upload/'), strlen($url));
    }

    private function txt2file($text, $filename) {
        self::checkFilename($filename);
        @file_put_contents($filename, $text, LOCK_EX);
    }

    private function checkFilename($filename){
        $position = strrpos($filename,'/');
        $path = substr($filename,0,$position);
        if(!file_exists($path)){
            mkdir(iconv("UTF-8", "GBK", $path),0777,true);
        }
    }

}