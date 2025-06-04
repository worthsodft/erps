<?php
/**
 * Created by vandles
 * Date: 2025/4/30
 * Time: 17:48
 */

namespace addon\base;

use Exception;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use think\admin\extend\JwtExtend;
use think\exception\HttpResponseException;
use vandles\lib\VException;
use vandles\lib\VResponse;

trait AddonBaseControllerTrait {
    /**
     * 插件名称
     * @var string
     */
    protected $addonName;
    /**
     * 插件操作类
     * @var BaseAddon
     */
    protected $addonHandler;

    public function initialize() {
        $this->setHandler();

        $this->addonName = $this->addonHandler->getName();
        try{
            if (!$this->addonHandler->isInstalled()) $this->error("插件未安装");
            if ($this->addonHandler->isDisable()) $this->error("插件未启用");
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
    }

    private function setHandler() {
        $file = app()->getAppPath() . 'info.yml';
        if (!file_exists($file)) $this->error("插件信息文件不存在");
        $yamlContent = file_get_contents($file);
        $info        = Yaml::parse($yamlContent);
        if (!isset($info['name'])) $this->error("插件信息文件缺少插件名称");
        $class              = "app\\addon" . $info['name'] . "\\" . ucfirst($info['name']);
        $this->addonHandler = $class::instance();
    }

    /**
     * 返回失败的内容
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function error1($info, $data = '{-null-}', $code = 0): void{
        $this->success($info, $data, $code);
    }

    /**
     * 返回成功的内容
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function success1($info, $data = '{-null-}', $code = 1): void{
        if ($data === '{-null-}') $data = new stdClass();
        $result = ['code' => $code, 'info' => is_string($info) ? lang($info) : $info, 'data' => $data];
        if (JwtExtend::isRejwt()) $result['token'] = JwtExtend::token();
        throw new HttpResponseException(json($result));
    }

}