<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\lib\ali;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use think\App;
use think\Service;
use vandles\Result;


class AliCloudMarket extends Service {

    private $config;

    public function __construct($config, App $app){
        parent::__construct($app);
        $this->app = $app;
        $this->config = $config;
    }
    /**
     * @param array $var
     * @param bool $new
     * @return AliCloudMarket
     */
    public static function instance(array $var = [], bool $new = false) {
        return app()->make(AliCloudMarket::class, $var, $new);
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

    //

    /**
     * 发送sms短信（验证码）
     * $config = [
     *     'key_id'        => 'LT12312nPDviAZTQ',
     *     'key_secret'    => '5lx5Fm21235mZ0vCZ2317w34gQ84F',
     *     'template_code' => 'SMS_161598123',
     *     'sms_sign'      => '短信签名'
     * ];
     *
     * @param $phone string 电话号码
     * @param $content string 发送内容
     * @return mixed|Result
     */
    public function sendSms($phone, $content) {
        if(!$this->config) return Result::error('缺少必要的配置参数');
        if (!$phone || !$content) return Result::error('手机号码或发送内容为空');
        $config = $this->config;
        $client = self::createClient($config['key_id'], $config['key_secret']);
        $sendSmsRequest = new SendSmsRequest([
            "phoneNumbers" => $phone,
            "signName" => $config['sms_sign'],
            "templateCode" => $config['template_code'],
            "templateParam" => json_encode(['code'=>$content])
        ]);
        $runtime = new RuntimeOptions([]);
        // 复制代码运行请自行打印 API 的返回值
        $res = $client->sendSmsWithOptions($sendSmsRequest, $runtime);
        if($res->body->code == 'OK') return Result::success('发送成功');
        else return Result::error($res->body->message . "({$res->body->code})");
    }

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Dysmsapi Client
     */
    private function createClient($accessKeyId, $accessKeySecret){
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }
}