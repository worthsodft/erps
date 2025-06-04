<?php
/**
 * Created by vandles
 * Date: 2025/5/6
 * Time: 10:28
 */

namespace vandles\lib;

use stdClass;
use think\admin\extend\JwtExtend;
use think\exception\HttpResponseException;

class VResponse {

    public static function success($info = '操作成功', $data = "{-null-}") {
        self::info($info, $data, 1);
    }
    public static function error($info = '操作失败', $data = "{-null-}") {
        self::info($info, $data, 0);
    }

    public static function info($info = "提示信息", $data = "{-null-}", $code = 1) {
        if ($data === '{-null-}') $data = new stdClass();
        $result = ['code' => $code, 'info' => is_string($info) ? lang($info) : $info, 'data' => $data];
        if (JwtExtend::isRejwt()) $result['token'] = JwtExtend::token();
        throw new HttpResponseException(json($result));
    }

}