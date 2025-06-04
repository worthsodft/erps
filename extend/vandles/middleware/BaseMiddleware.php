<?php
/**
 * Created by PhpStorm
 * User: vandles
 * Date: 2023/7/17
 * Time: 11:07
 * Email: windows_1122334@126.com
 */


namespace vandles\middleware;


use stdClass;
use think\admin\extend\JwtExtend;
use think\exception\HttpResponseException;
use think\Middleware;

class BaseMiddleware{

    /**
     * 返回失败的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function error($info, $data = '{-null-}', $code = 0): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        throw new HttpResponseException(json([
            'code' => $code, 'info' => $info, 'data' => $data,
        ]));
    }

    /**
     * 返回成功的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function success($info, $data = '{-null-}', $code = 1): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        $result = ['code' => $code, 'info' => $info, 'data' => $data];
        if (JwtExtend::isRejwt()) $result['token'] = JwtExtend::token();
        throw new HttpResponseException(json($result));
    }
}