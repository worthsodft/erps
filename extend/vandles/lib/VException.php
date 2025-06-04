<?php
/**
 * Created by PhpStorm
 * User: vandles
 * Date: 2023/7/16
 * Time: 22:06
 * Email: windows_1122334@126.com
 */


namespace vandles\lib;


class VException {

    protected static $errors = [

        8401 => 'token不存在',

        9997 => '数据表已存在',
        9998 => '自定义错误',
        9999 => '未知错误'
    ];

    public static function throw($code) {
        if (is_numeric($code)) $msg = self::$errors[$code] ?? self::$errors[9999];
        else {
            $msg  = $code;
            $code = 9998;
        }
        if(!is_string($msg)) {
            $msg = "异常信息必须为字符串，当前类型：" . gettype($msg);
            $code = 9998;
        }
        throw new \Exception($msg, $code);
    }

    public static function runtime($code) {
        if (is_numeric($code)) $msg = self::$errors[$code] ?? self::$errors[9999];
        else {
            $msg  = $code;
            $code = 9998;
        }
        if(!is_string($msg)) {
            $msg = "异常信息必须为字符串，当前类型：" . gettype($msg);
            $code = 9998;
        }
        throw new \RuntimeException($msg, $code);
    }
}