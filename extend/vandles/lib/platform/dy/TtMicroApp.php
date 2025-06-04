<?php
/*
 * 
 * Author: vandles
 * Date: 2022/6/27 16:15
 * Email: <vandles@qq.com>
 */


namespace vandles\lib\dy;


use think\App;

class TtMicroApp {
    private $app;
    private $config;

    public function __construct($config, App $app){
        $this->app = $app;
        $this->config = [
            'appid'  => $config['app_id'],
            'secret' => $config['app_secret'],
            'pay_token' => $config['pay_token'],
            'pay_salt'  => $config['pay_salt'],
        ];
    }
    /**
     * @param array $var
     * @param bool $new
     * @return TtMicroApp
     */
    public static function instance(array $var = [], bool $new = false) {
        return app()->make(static::class, $var, $new);
    }

    public function getConfig() {return $this->config;}

    /**
     * 获取openid, unionid, session_key
     * @param $code
     * @return mixed
     */
    public function session($code) {
        $url = "https://developer.toutiao.com/api/apps/v2/jscode2session";
        $opts = [
            'code' => $code,
        ];
        $opts = array_merge($this->config, $opts);
        $res = $this->httpPost($url, $opts);
        return $res;
    }

    /**
     * 解密数据
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return array
     * @throws /Exception
     */
    public function decrypt($code, $iv, $encryptedData) {
        $res = $this->session($code);

        if($res['err_no'] != 0)
            throw new \Exception('解密数据失败:'. $res['err_tips'] . "($res[err_no])");
        $openid = $res['data']['openid'];

        $data_   = base64_decode($encryptedData);
        $aesKey_ = base64_decode($res['data']['session_key']);
        $iv_     = base64_decode($iv);

        $res = openssl_decrypt($data_, "AES-128-CBC", $aesKey_, 1, $iv_);

        $res = json_decode($res, true);
        $res['openId'] = $openid;
        return $res;
    }

    // 预下单
    public function orderInfo($opts) {
        $url = "https://developer.toutiao.com/api/apps/ecpay/v1/create_order";
        $opts['app_id'] = $this->config['appid'];

        $opts['sign'] = $this->sign($opts);
        $res = $this->httpPost($url, $opts);
        return $res;
    }

    /**
     * 支付回调
     * 通知失败的重试时间间隔:15s/15s/30s/3m/10m/20m/30m/30m/30m/60m/3h/3h/3h/6h/6h
     * @return mixed
     */
    public function notify() {
        $post = request()->post();
        $sign = $this->sign($post, $this->config['pay_token'], 'sha1', '');
        if($post['type'] == 'payment') $title = '支付';
        elseif($post['type'] == 'refund') $title = '退款';
        else $title = '未知类型';
        if($sign === $post['msg_signature']){
            alert("字节 $title 回调");
            alert($post);
            return json_decode($post['msg'], 1);
        }else{
            error("非法字节 $title 回调：签名不匹配");
            error($post);
            echo 'success';die;
        }
    }

    /**
     * 退款
     * @param array $opts
     * @return mixed
     */
    public function refund(array $opts) {
        $url = "https://developer.toutiao.com/api/apps/ecpay/v1/create_refund";
        $opts['app_id'] = $this->config['appid'];

        $opts['sign'] = $this->sign($opts);
        $res = $this->httpPost($url, $opts);
        return $res;
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
    private function httpPost($url, $params) {
        $params = json_encode($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length:' . strlen($params)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    private function sign($opts, $token = null, $type='md5', $separate="&") {
        $rList = array();
        foreach($opts as $k =>$v) {
            if ($k == "app_id" || $k == "sign" || $k == "thirdparty_id" || $k == 'msg_signature' || $k == 'type')
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0,1)=="\"" && substr($value,$len, $len-1)=="\"")
                $value = substr($value,1, $len-1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        if($token === null) $token = $this->config['pay_salt'];
        array_push($rList, $token);
        sort($rList, 2);
        return $type(implode($separate, $rList));
    }
}