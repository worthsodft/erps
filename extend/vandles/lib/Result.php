<?php


namespace vandles\lib;


use ArrayAccess;
use think\Container;
use vandles\exception\JsonException;

class Result implements ArrayAccess{

    public $code = 0;
    public $info = '';
    public $data = [];

    /**
     * 静态实例对象
     * @param array $var 实例参数
     * @param boolean $new 创建新实例
     * @return static|mixed
     */
    public static function instance(array $var = [], bool $new = false){
        return Container::getInstance()->make(static::class, $var, $new);
    }

    public static function info(int $code, string $info='', array $data=[]) {
        $instance = self::instance();
        $instance->code = $code;
        $instance->info = $info;
        $instance->data = $data;

        return $instance;
    }

    public static function error(string $info='', array $data=[]){
        return static::info(0, $info, $data);
    }
    public static function success(string $info='', array $data=[]){
        return static::info(1, $info, $data);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetUnset($offset) {
        if (isset($this->$offset)) unset($this->$offset);
    }
	public function toArray() {
        return json_decode(json_encode($this),true);
    }
}