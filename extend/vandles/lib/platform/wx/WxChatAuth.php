<?php
/*
 * 
 * Author: vandles
 * Date: 2022/11/22 8:50
 * Email: <vandles@qq.com>
 */


namespace vandles\lib\platform\wx;


use vandles\exception\JsonException;
use vandles\lib\platform\Base;
use vandles\lib\Tool;

class WxChatAuth extends Base {

    protected function initialize() {
        if (empty($this->config['appid'])) throw JsonException::exception(80001);
        if (empty($this->config['appsecret'])) throw JsonException::exception(80002);
    }

    /**
     * 静默登录
     * @param string $url
     * @return mixed
     */
    public function silence($url='') {
        if(!($code = input('code'))){
            $url = $this->buildRedirectUrl($url);
            header('location:'.$url);
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->config['appid']}&secret={$this->config['appsecret']}&code={$code}&grant_type=authorization_code";
        $res = Tool::httpGet($url);
        return $res;
    }

    /**
     * 构建跳转路径
     * @param string $url
     * @param string $scope
     * @return string
     */
    private function buildRedirectUrl($url='', $scope='snsapi_base') {
        $redirect_uri = urlencode(url($url)->domain(true)->build());
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->config['appid']}&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=STATE#wechat_redirect";
        return $url;
    }



}