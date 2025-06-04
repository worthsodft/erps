<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\extend\HttpExtend;
use think\admin\helper\QueryHelper;
use think\helper\Str;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\MyAddonModel;

use vandles\service\BaseService;

/**
 * 远程调用插件中心的接口
 */
class AddonCenterService{
    protected static $instance;
    protected $baseUrl;


    public static function instance(): AddonCenterService {
        if (!static::$instance) {
            static::$instance = new static();
            $baseUrl = config('a.addoncenter_url');
            $path = "/addoncenter/api/";
            static::$instance->baseUrl = $baseUrl . $path;
        }
        return static::$instance;
    }

    /**
     * @return BaseSoftDeleteModel|MyAddonModel
     */
    public function getModel() {
        return null;
    }

    /**
     * 获取分页列表
     * @return array
     */
    public function getAddon($page=1, $limit=20) {
        $url = $this->baseUrl . "addon";
        $params = [
            'page' => $page,
            'limit' => $limit,
        ];
        $opts = [
            "headers" => [
                "token: " . $this->getToken(),
            ],
        ];
        // dd($opts);
        $res = $this->httpGet($url, $params, $opts);
        return $res['data']['pageData']??null;
    }

    /**
     * 按id获取1个
     * @param int $id
     * @return array
     */
    public function getAddonById(int $id) {
        $url = $this->baseUrl . "addon/{$id}";
        $opts = [
            "headers" => [
                "token: " . $this->getToken(),
            ],
        ];
        $res = $this->httpGet($url, [], $opts);
        return $res['data']['addon']??null;
    }

    // /**
    //  * 新增
    //  * @return array
    //  */
    // public function postAddon($data) {
    //     $url = $this->baseUrl . "addon";
    //     $res = [];
    //
    //     return $res;
    //
    // }

    // /**
    //  * 按id修改
    //  * @param int $id
    //  * @return array
    //  */
    // public function putAddonById(int $id, $data) {
    //     $url = $this->baseUrl . "addon";
    //     $res = [];
    //
    //     return $res;
    //
    // }

    // /**
    //  * 按id删除
    //  * @param int $id
    //  * @return array
    //  */
    // public function deleteAddonById(int $id) {
    //     $url = $this->baseUrl . "addon";
    //     $res = [];
    //
    //     return $res;
    //
    // }


    /**
     * 获取插件压缩包的二进制内容
     * @param $id
     * @return mixed|null
     */
    public function getZipContentById($id) {
        $url = $this->baseUrl . "content/{$id}";
        $opts = [
            "headers" => [
                "token: " . $this->getToken(),
            ],
        ];
        $res = $this->httpGet($url, [], $opts);
        return $res['data']['content']??null;
    }

    private function getToken() {
        $url = $this->baseUrl . "login";
        $params = [
            'username' => 'tom',
            'password' => '123123'
        ];
        $key = md5(json_encode($params) . $url);
        $token = cache($key);
        if($token) return $token;

        $res = $this->httpPost($url, $params);
        if(!isset($res['data']['token'])) VException::throw("token获取错误");
        $token = $res['data']['token'];
        cache($key, $token, $res['data']['interval']);
        return $token;
    }

    /**
     * @param string $url
     * @param array|string $params
     * @param array $opts
     * @return mixed
     */
    private function httpGet(string $url, $params, array $opts=[]) {
        $opts['headers'] = array_merge($opts['headers']??[], ["ngrok-skip-browser-warning: any"]);
        $res = http_get($url, $params, $opts);
        $result = json_decode($res, true);
        if(!$result) VException::throw("请求失败: ". $res);
        return $result;
    }

    /**
     * @param string $url
     * @param array|string $params
     * @param array $opts
     * @return mixed
     */
    private function httpPost(string $url, $params, array $opts=[]) {
        $opts['headers'] = array_merge($opts['headers']??[], ["ngrok-skip-browser-warning: any"]);
        $res = http_post($url, $params, $opts);
        $result = json_decode($res, true);
        if(!$result) VException::throw("请求失败: ". $res);
        return $result;
    }

    private function httpPut(string $url, array $params, array $opts) {
        $opts['headers'] = array_merge($opts['headers']??[], ["ngrok-skip-browser-warning: any"]);
        $opts['data'] = $params;
        $res = HttpExtend::request('put', $url, $opts);
        $result = json_decode($res, true);
        if(!$result) VException::throw("请求失败: ". $res);
        return $result;
    }

}