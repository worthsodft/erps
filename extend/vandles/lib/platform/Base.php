<?php
/*
 * 
 * Author: vandles
 * Date: 2022/11/22 8:43
 * Email: <vandles@qq.com>
 */


namespace vandles\lib\platform;

/**
 * Class Base
 * @package vandles\lib\platform
 *
 * @method getUserIdByAuthCode($authCode)
 *
 *
 *
 */
abstract class Base {
    /** @var Base */
    protected static $instance = null;
    protected $config = [];

    /**
     * 初始化
     * @return mixed
     */
    abstract protected function initialize();


    /**
     * 静默登录
     * @param string $url 重定向地址
     * @return mixed
     */
    abstract public function silence($url);


    /**
     * 单例实例化类
     * @param $config
     * @return Base
     */
    public static function instance($config) {
        if(!static::$instance){
            static::$instance = new static();
            static::$instance->config = $config;
            static::$instance->initialize();
        }
        return static::$instance;
    }

    public function getConfig():array{
        return $this->config;
    }
}