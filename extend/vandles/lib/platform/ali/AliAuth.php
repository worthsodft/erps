<?php
/*
 * 
 * Author: vandles
 * Date: 2022/11/24 15:44
 * Email: <vandles@qq.com>
 */


namespace vandles\lib\platform\ali;


use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use vandles\exception\JsonException;
use vandles\lib\platform\Base;

class AliAuth extends Base{

    public function initialize(){
        if (empty($this->config['appid'])) throw JsonException::exception(80017);
        if (empty($this->config['app_cert'])) throw JsonException::exception(80018);
        if (empty($this->config['alipay_cert'])) throw JsonException::exception(80019);
        if (empty($this->config['alipay_root'])) throw JsonException::exception(80020);
        if (empty($this->config['private_key'])) throw JsonException::exception(80021);
        if (!is_file($this->config['app_cert'])) throw JsonException::exception(80022);
        if (!is_file($this->config['alipay_cert'])) throw JsonException::exception(80023);
        if (!is_file($this->config['alipay_root'])) throw JsonException::exception(80024);

        Factory::setOptions($this->getOptions());
    }

    private function getOptions(){
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $options->appId = $this->config['appid'];

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $this->config['private_key'];

        $options->alipayCertPath = $this->config['alipay_cert'];
        $options->alipayRootCertPath = $this->config['alipay_root'];
        $options->merchantCertPath = $this->config['app_cert'];

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        // $options->alipayPublicKey = '<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->';

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = "<-- 请填写您的支付类接口异步通知接收服务地址，例如：https://www.test.com/callback -->";

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->";

        return $options;
    }

    /**
     * 静默登录
     * @return mixed
     */
    public function silence($url) {

    }

    /**
     * 得到支付宝userId
     * @param $authCode
     * @return string
     * @throws \Exception
     */
    public function getUserIdByAuthCode($authCode) {
        $res = Factory::base()->oauth()->getToken($authCode);
        return $res->userId;
    }
}