<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\lib;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use think\App;
use vandles\lib\Result;


class LingKaiSms {

    private $config;

    public function __construct($config){
        $this->config = $config;
    }
    /**
     * @param array $var
     * @param bool $new
     * @return LingKaiSms
     */
    public static function instance(array $var = [], bool $new = false) {
        return app()->make(LingKaiSms::class, $var, $new);
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

    //

    /**
     * 发送sms短信（验证码）
     * $config = [
     *     'CorpID' => 'XXXX',
     *     'Pwd'    => 'XXX',
     * ];
     *
     * $smsService = LingKaiSmsService::instance(compact('confi
     * $code = random_int(1000, 9999);
     * $code = 8888;
     * $res = $smsService->sendSms('13381178280', $code);
     *
     * @param $phone string 电话号码
     * @param $content string 发送内容
     * @return mixed|Result
     */
    public function sendSms($phone, $content) {
        $data = $this->config;
        $data['Mobile'] = $phone;
        $data['Content'] = iconv('UTF-8', 'GB2312', '您的验证码为: '.$content.' ,该验证码 15 分钟内有效，请勿泄漏于他人。【豪杰林立】');
        $url = "https://inolink.com/ws/BatchSend2.aspx";
        $res = $this->httpGet($url, $data);
        if($res > 0) return Result::success('发送成功', compact('phone','content'));
        switch($res){
            case -1: $msg = '账号未注册';break;
            case -2: $msg = '网络访问超时，请稍后再试';break;
            case -3: $msg = '帐号或密码错误';break;
            case -4: $msg = '只支持单发';break;
            case -5: $msg = '余额不足，请充值';break;
            case -6: $msg = '定时发送时间不是有效的时间格式';break;
            case -7: $msg = '提交信息末尾未签名，请添加中文的企业签名【 】或未采用gb2312编码';break;
            case -8: $msg = '发送内容需在1到300字之间';break;
            case -9: $msg = '发送号码为空';break;
            case -10: $msg  = '定时时间不能小于系统当前时间';break;
            case -11: $msg  = '屏蔽手机号码';break;
            case -100: $msg = '限制IP访问';break;
            default: $msg   = '未知错误';
        }
        return Result::error($msg);
    }
    private function httpGet($url, $params=null) {
        if($params){
            if(strpos($url, '?') !== false)
                $url .= '&'.http_build_query($params);
            else
                $url .= '?'.http_build_query($params);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

}